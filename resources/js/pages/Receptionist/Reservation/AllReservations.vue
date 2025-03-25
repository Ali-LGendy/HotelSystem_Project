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
          <a
            href="/receptionist/reservations"
            class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white transition hover:bg-indigo-700"
          >
            Pending Reservations
          </a>
          <a
            href="/receptionist/reservations/create"
            class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white transition hover:bg-blue-700"
          >
            New Reservation
          </a>
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
              <a
                :href="`/receptionist/reservations/${row.id}`"
                class="rounded-md border border-gray-600 bg-gray-700 px-3 py-1 text-sm font-medium text-gray-200 hover:bg-gray-600"
              >
                View
              </a>
              <a
                :href="`/receptionist/reservations/${row.id}/edit`"
                class="rounded-md border border-gray-600 bg-gray-700 px-3 py-1 text-sm font-medium text-gray-200 hover:bg-gray-600"
              >
                Edit
              </a>
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



      <!-- Delete Confirmation Dialog -->
      <div v-if="showDeleteDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="w-full max-w-md rounded-lg bg-gray-800 p-6 text-gray-200 shadow-xl">
          <h3 class="text-xl font-semibold text-gray-100">Confirm Deletion</h3>
          <p class="mt-2 text-gray-400">
            Are you sure you want to delete this reservation? This action cannot be undone.
          </p>
          <div class="mt-6 flex justify-end space-x-3">
            <button
              class="rounded-md border border-gray-600 bg-gray-700 px-4 py-2 text-sm font-medium text-gray-200 hover:bg-gray-600"
              @click="cancelDelete"
            >
              Cancel
            </button>
            <button
              class="rounded-md bg-red-700 px-4 py-2 text-sm font-medium text-white hover:bg-red-600"
              @click="deleteReservation"
            >
              Delete
            </button>
          </div>
        </div>
      </div>

      <!-- Approve Confirmation Dialog -->
      <div v-if="showApproveDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="w-full max-w-md rounded-lg bg-gray-800 p-6 text-gray-200 shadow-xl">
          <h3 class="text-xl font-semibold text-gray-100">Confirm Approval</h3>
          <p class="mt-2 text-gray-400">
            Are you sure you want to approve this reservation?
          </p>
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
import { router } from '@inertiajs/vue3';
import DataTable from '@/components/ui/DataTable.vue';

// Props
const props = defineProps({
  reservations: {
    type: Object,
    required: true
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
    header: 'Client Name'
  },
  {
    accessorKey: 'room.room_number',
    header: 'Room Number'
  },
  {
    accessorKey: 'accompany_number',
    header: 'Accompany Number'
  },
  {
    accessorKey: 'price_paid',
    header: 'Paid Price'
  },
  {
    accessorKey: 'check_in_date',
    header: 'Check-in Date'
  },
  {
    accessorKey: 'check_out_date',
    header: 'Check-out Date'
  },
  {
    accessorKey: 'status',
    header: 'Status'
  },
  {
    id: 'actions',
    header: 'Actions',
    enableSorting: false
  }
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
    'confirmed': 'bg-green-900 text-green-200',
    'checked_in': 'bg-blue-900 text-blue-200',
    'checked-in': 'bg-blue-900 text-blue-200',
    'checked_out': 'bg-gray-700 text-gray-200',
    'checked-out': 'bg-gray-700 text-gray-200',
    'pending': 'bg-yellow-900 text-yellow-200',
    'cancelled': 'bg-red-900 text-red-200'
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
    replace: true
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
  
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = `/receptionist/reservations/${selectedReservationId.value}`;
  
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const csrfInput = document.createElement('input');
  csrfInput.type = 'hidden';
  csrfInput.name = '_token';
  csrfInput.value = csrfToken;
  
  const methodInput = document.createElement('input');
  methodInput.type = 'hidden';
  methodInput.name = '_method';
  methodInput.value = 'DELETE';
  
  form.appendChild(csrfInput);
  form.appendChild(methodInput);
  document.body.appendChild(form);
  form.submit();
};

const approveReservation = (id) => {
  selectedReservationId.value = id;
  showApproveDialog.value = true;
};

const cancelApprove = () => {
  showApproveDialog.value = false;
  selectedReservationId.value = null;
};

const confirmApproveReservation = () => {
  if (!selectedReservationId.value) return;
  
  // Find the reservation data
  const reservation = props.reservations.data.find(r => r.id === selectedReservationId.value);
  if (!reservation) return;
  
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = `/receptionist/reservations/${selectedReservationId.value}`;
  
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const csrfInput = document.createElement('input');
  csrfInput.type = 'hidden';
  csrfInput.name = '_token';
  csrfInput.value = csrfToken;
  
  const methodInput = document.createElement('input');
  methodInput.type = 'hidden';
  methodInput.name = '_method';
  methodInput.value = 'PUT';
  
  // Add all required fields
  const createInput = (name, value) => {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = name;
    input.value = value;
    return input;
  };
  
  form.appendChild(csrfInput);
  form.appendChild(methodInput);
  form.appendChild(createInput('status', 'confirmed'));
  form.appendChild(createInput('room_id', reservation.room_id));
  form.appendChild(createInput('accompany_number', reservation.accompany_number));
  form.appendChild(createInput('check_in_date', reservation.check_in_date));
  form.appendChild(createInput('check_out_date', reservation.check_out_date));
  form.appendChild(createInput('price_paid', reservation.price_paid));
  form.appendChild(createInput('client_id', reservation.client_id));
  
  document.body.appendChild(form);
  form.submit();
};
</script>

<style scoped>
.pagination-link {
  @apply px-3 py-1 rounded-md text-sm;
}

.pagination-link-active {
  @apply bg-blue-600 text-white;
}

.pagination-link-inactive {
  @apply bg-gray-700 text-gray-200 hover:bg-gray-600;
}

.pagination-link-disabled {
  @apply bg-gray-800 text-gray-500 cursor-not-allowed;
}
</style>