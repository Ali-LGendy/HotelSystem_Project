<template>
  <div class="mx-auto max-w-7xl px-4 py-8">
    <div class="rounded-lg bg-gray-900 p-8 text-gray-200 shadow-lg">
      <!-- Header with Navigation -->
      <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h2 class="text-3xl font-bold">All Reservations</h2>
          <p class="mt-2 text-gray-400">Showing all reservations in the system</p>
        </div>
        <div class="flex flex-wrap gap-3">
          <Link
            v-if="isAdmin"
            href="/receptionist/reservations"
            class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white transition hover:bg-indigo-700"
          >
            Pending Reservations
          </Link>
          <Link
            href="/receptionist/clients/my-approved"
            class="rounded-lg bg-green-600 px-4 py-2 font-semibold text-white transition hover:bg-green-700"
          >
            My Approved Clients
          </Link>
        </div>
      </div>

      <div v-if="reservations.data.length === 0" class="text-center py-8">
        <p class="text-lg text-gray-300">No reservations found.</p>
      </div>

      <!-- Data Table -->
      <div v-else class="rounded-lg border border-gray-700 bg-gray-800 overflow-hidden">
        <DataTable
          :columns="columns"
          :data="reservations.data"
          :pagination="{
            pageSize: 10,
            pageIndex: currentPage - 1,
            totalItems: reservations.total,
            manualPagination: true
          }"
          @page-change="handlePageChange"
          class="text-gray-200"
        >
          <!-- Client Name Cell Template -->
          <template #cell-client.name="{ row }">
            {{ row.client ? row.client.name : 'N/A' }}
          </template>

          <!-- Room Number Cell Template -->
          <template #cell-room.room_number="{ row }">
            {{ row.room ? row.room.room_number : 'N/A' }}
          </template>

          <!-- Price Cell Template -->
          <template #cell-price_paid="{ row }">
            ${{ row.price_paid }}
          </template>

          <!-- Check-in Date Cell Template -->
          <template #cell-check_in_date="{ row }">
            {{ formatDate(row.check_in_date) }}
          </template>

          <!-- Check-out Date Cell Template -->
          <template #cell-check_out_date="{ row }">
            {{ formatDate(row.check_out_date) }}
          </template>

          <!-- Status Cell Template -->
          <template #cell-status="{ row }">
            <span
              :class="[
                'px-2 py-1 text-xs font-medium rounded-full',
                getStatusClass(row.status)
              ]"
            >
              {{ row.status }}
            </span>
          </template>

          <!-- Actions Cell Template -->
          <template #cell-actions="{ row }">
            <div class="flex space-x-2">
              <button
                v-if="row.status === 'pending'"
                @click="approveReservation(row.id)"
                class="rounded-md bg-green-700 px-3 py-1 text-sm font-medium text-white hover:bg-green-600"
              >
                Approve
              </button>
              <button
                @click="navigateTo(`/receptionist/reservations/${row.id}`)"
                class="rounded-md border border-gray-600 bg-gray-700 px-3 py-1 text-sm font-medium text-gray-200 hover:bg-gray-600"
              >
                View
              </button>
              <button
                @click="navigateTo(`/receptionist/reservations/${row.id}/edit`)"
                class="rounded-md border border-gray-600 bg-gray-700 px-3 py-1 text-sm font-medium text-gray-200 hover:bg-gray-600"
              >
                Edit
              </button>
              <button
                @click="confirmDelete(row.id)"
                class="rounded-md bg-red-800 px-3 py-1 text-sm font-medium text-white hover:bg-red-700"
              >
                Delete
              </button>
            </div>
          </template>
        </DataTable>
      </div>

      <!-- Reservation Statistics Dashboard -->
      <div class="mt-8 p-6 bg-gray-800 rounded-lg border border-gray-700">
        <h3 class="text-xl font-semibold mb-4 text-gray-100">Reservation Statistics</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div class="p-4 rounded-lg bg-gray-700">
            <div class="text-sm text-gray-400">Total Pending Reservations</div>
            <div class="text-2xl font-bold text-gray-100">{{ reservationStats.totalPending }}</div>
          </div>
          <div class="p-4 rounded-lg bg-blue-900">
            <div class="text-sm text-gray-300">Confirmed Reservations</div>
            <div class="text-2xl font-bold text-gray-100">{{ reservationStats.confirmedReservations }}</div>
          </div>
          <div class="p-4 rounded-lg bg-green-900">
            <div class="text-sm text-gray-300">Checked-in Guests</div>
            <div class="text-2xl font-bold text-gray-100">{{ reservationStats.checkedInGuests }}</div>
          </div>
          <div class="p-4 rounded-lg bg-purple-900">
            <div class="text-sm text-gray-300">Total Revenue</div>
            <div class="text-2xl font-bold text-gray-100">${{ reservationStats.totalRevenue }}</div>
          </div>
        </div>
      </div>

      <!-- Delete Confirmation Dialog -->
      <div v-if="showDeleteDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="w-full max-w-md rounded-lg bg-gray-800 p-6 text-gray-200 shadow-xl">
          <h3 class="text-xl font-semibold text-gray-100">Confirm Deletion</h3>
          <p class="mt-2 text-gray-400">Are you sure you want to delete this reservation? This action cannot be undone.</p>
          <div class="mt-6 flex justify-end space-x-3">
            <button
              class="rounded-md border border-gray-600 bg-gray-700 px-4 py-2 text-sm font-medium text-gray-200 hover:bg-gray-600"
              @click="cancelDelete"
            >
              Cancel
            </button>
            <button class="rounded-md bg-red-700 px-4 py-2 text-sm font-medium text-white hover:bg-red-600" @click="deleteReservation">
              Delete
            </button>
          </div>
        </div>
      </div>

      <!-- Approve Confirmation Dialog -->
      <div v-if="showApproveDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="w-full max-w-md rounded-lg bg-gray-800 p-6 text-gray-200 shadow-xl">
          <h3 class="text-xl font-semibold text-gray-100">Confirm Approval</h3>
          <p class="mt-2 text-gray-400">Are you sure you want to approve this reservation?</p>
          <div class="mt-6 flex justify-end space-x-3">
            <button
              class="rounded-md border border-gray-600 bg-gray-700 px-4 py-2 text-sm font-medium text-gray-200 hover:bg-gray-600"
              @click="cancelApprove"
            >
              Cancel
            </button>
            <button
              class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-600"
              @click="confirmApproveReservation"
            >
              Approve
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import axios from 'axios';
import DataTable from '@/components/ui/DataTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
defineOptions({ layout: AppLayout });
// Props
const props = defineProps({
  reservations: {
    type: Object,
    required: true
  },
  reservationStats: {
    type: Object,
    default: () => ({
      totalPending: 0,
      confirmedReservations: 0,
      checkedInGuests: 0,
      totalRevenue: 0
    })
  },
  isAdmin: {
    type: Boolean,
    default: false
  }
});

// State
const showDeleteDialog = ref(false);
const showApproveDialog = ref(false);
const selectedReservationId = ref(null);

// Table columns definition
const columns = [
    {
        accessorKey: 'client.name',
        header: 'Client Name',
    },
    {
        accessorKey: 'room.room_number',
        header: 'Room Number',
    },
    {
        accessorKey: 'accompany_number',
        header: 'Accompany Number',
    },
    {
        accessorKey: 'price_paid',
        header: 'Paid Price',
    },
    {
        accessorKey: 'check_in_date',
        header: 'Check-in Date',
    },
    {
        accessorKey: 'check_out_date',
        header: 'Check-out Date',
    },
    {
        accessorKey: 'status',
        header: 'Status',
    },
    {
        id: 'actions',
        header: 'Actions',
        enableSorting: false,
    },
];

// Computed
const currentPage = computed(() => {
    return props.reservations.current_page || 1;
});

// Methods
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString();
    } catch (e) {
        return dateString;
    }
};

const getStatusClass = (status) => {
    const classes = {
        confirmed: 'bg-green-900 text-green-200',
        checked_in: 'bg-blue-900 text-blue-200',
        'checked-in': 'bg-blue-900 text-blue-200',
        checked_out: 'bg-gray-700 text-gray-200',
        'checked-out': 'bg-gray-700 text-gray-200',
        pending: 'bg-yellow-900 text-yellow-200',
        cancelled: 'bg-red-900 text-red-200',
    };
    return classes[status] || 'bg-gray-700 text-gray-200';
};

const handlePageChange = (pageIndex) => {
    const page = pageIndex + 1;
    const baseUrl = `/receptionist/all-reservations`;

    // Build query parameters
    const params = new URLSearchParams();
    params.append('page', page);

    router.visit(`${baseUrl}?${params.toString()}`, {
        preserveScroll: true,
        preserveState: false,
        replace: true,
    });
};

const confirmDelete = (id) => {
    selectedReservationId.value = id;
    showDeleteDialog.value = true;
};

const cancelDelete = () => {
    showDeleteDialog.value = false;
    selectedReservationId.value = null;
};

const deleteReservation = () => {
  if (!selectedReservationId.value) return;

  router.delete(`/receptionist/reservations/${selectedReservationId.value}`, {}, {
    onSuccess: () => {
      showDeleteDialog.value = false;
      selectedReservationId.value = null;
    },
    onError: (errors) => {
      console.error('Error deleting reservation:', errors);
    }
  });
};

const approveReservation = (id) => {
    selectedReservationId.value = id;
    showApproveDialog.value = true;
};

const cancelApprove = () => {
    showApproveDialog.value = false;
    selectedReservationId.value = null;
};

// Navigation method using Inertia
const navigateTo = (url) => {
  router.get(url, {}, {
    preserveScroll: false,
    preserveState: false,
    replace: false
  });
};

const confirmApproveReservation = async () => {
  if (!selectedReservationId.value) return;

  // Find the reservation data
  const reservation = props.reservations.data.find(r => r.id === selectedReservationId.value);
  if (!reservation) return;

  // Prepare data for the update
  const data = {
    status: 'confirmed',
    room_id: reservation.room_id,
    accompany_number: reservation.accompany_number,
    check_in_date: reservation.check_in_date,
    check_out_date: reservation.check_out_date,
    price_paid: reservation.price_paid,
    client_id: reservation.client_id
  };

  try {
    // Use axios directly instead of Inertia to handle the JSON response
    const response = await axios.put(`/receptionist/reservations/${selectedReservationId.value}`, data, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });

    // Check if the request was successful
    if (response.data.success) {
      alert('Reservation approved successfully!');

      // Close the dialog
      showApproveDialog.value = false;
      selectedReservationId.value = null;

      // Check if client is already approved
      const clientApproved = response.data.client_approved;

      // Refresh the current page to show updated data
      if (reservation.client_id && !clientApproved) {
        // If client is not approved, redirect to clients page
        const baseUrl = `/receptionist/clients`;
        router.visit(baseUrl, {
          preserveScroll: false,
          preserveState: false,
          replace: false
        });
      } else {
        // Otherwise, refresh the current page with updated data
        window.location.reload(); // Full page reload to ensure data is refreshed
      }
    } else {
      alert('Could not approve reservation. Please try again.');
    }
  } catch (error) {
    console.error('Error approving reservation:', error);

    // Show more detailed error message if available
    if (error.response && error.response.data && error.response.data.message) {
      alert(`Error: ${error.response.data.message}`);
    } else {
      alert('Could not approve reservation due to a technical issue. Please try refreshing the page.');
    }

    // Close the dialog
    showApproveDialog.value = false;
    selectedReservationId.value = null;
  }
};
</script>

<style scoped>
.pagination-link {
    @apply rounded-md px-3 py-1 text-sm;
}

.pagination-link-active {
    @apply bg-blue-600 text-white;
}

.pagination-link-inactive {
    @apply bg-gray-700 text-gray-200 hover:bg-gray-600;
}

.pagination-link-disabled {
    @apply cursor-not-allowed bg-gray-800 text-gray-500;
}
</style>
