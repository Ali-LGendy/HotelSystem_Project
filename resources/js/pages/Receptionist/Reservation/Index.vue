<template>
  <div class="mx-auto max-w-7xl px-4 py-8">
    <div class="rounded-lg bg-gray-900 p-8 text-gray-200 shadow-lg">
      <!-- Header with Navigation -->
      <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h2 class="text-3xl font-bold">{{ showAll ? 'All Reservations' : 'Pending Reservations' }}</h2>
          <p class="mt-2 text-gray-400">{{ showAll ? 'Showing all reservations in the system' : 'Showing reservations that need your approval' }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
          <a
            v-if="!showAll"
            href="/receptionist/all-reservations"
            class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white transition hover:bg-indigo-700"
          >
            All Reservations
          </a>
          <a
            v-else
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
        <p class="text-lg text-gray-300">{{ showAll ? 'No reservations found.' : 'No pending reservations found.' }}</p>
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
          <!-- Status Cell Template -->
          <template #cell-status="{ row }">
            <Badge :variant="getStatusVariant(row.status)" class="text-xs font-medium">
              {{ row.status }}
            </Badge>
          </template>

          <!-- Actions Cell Template -->
          <template #cell-actions="{ row }">
            <div class="flex items-center gap-2">
              <button
                v-if="row.original && row.original.status === 'pending'"
                class="rounded-md bg-green-700 px-3 py-1 text-sm font-medium text-white hover:bg-green-600"
                @click.prevent="approveReservation(row)"
              >
                Approve
              </button>
              <a
                :href="'/receptionist/reservations/' + (row.original ? row.original.id : row.id)"
                class="rounded-md border border-gray-600 bg-gray-700 px-3 py-1 text-sm font-medium text-gray-200 hover:bg-gray-600"
              >
                View
              </a>
              <a
                :href="'/receptionist/reservations/' + (row.original ? row.original.id : row.id) + '/edit'"
                class="rounded-md border border-gray-600 bg-gray-700 px-3 py-1 text-sm font-medium text-gray-200 hover:bg-gray-600"
              >
                Edit
              </a>
              <button
                class="rounded-md bg-red-800 px-3 py-1 text-sm font-medium text-white hover:bg-red-700"
                @click="confirmDelete(row)"
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
              @click="showDeleteDialog = false"
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
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import DataTable from '@/components/ui/DataTable.vue';
import { Badge } from '@/components/ui/badge';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

// Props
const props = defineProps({
  reservations: {
    type: Object,
    required: true
  },
  showAll: {
    type: Boolean,
    default: false
  }
});

// State
const showDeleteDialog = ref(false);
const selectedReservation = ref(null);

// Navigation Links
const navigationLinks = [
  {
    href: '/receptionist/all-reservations',
    text: 'All Reservations',
    bgClass: 'bg-purple-600 text-white hover:bg-purple-700'
  },
  {
    href: '/receptionist/clients',
    text: 'Manage Clients',
    bgClass: 'bg-blue-600 text-white hover:bg-blue-700'
  },
  {
    href: route('receptionist.clients.my-approved'),
    text: 'My Approved Clients',
    bgClass: 'bg-green-600 text-white hover:bg-green-700'
  },
  {
    href: route('receptionist.clients.reservations'),
    text: 'Clients Reservations',
    bgClass: 'bg-purple-600 text-white hover:bg-purple-700'
  },
  {
    href: route('receptionist.reservations.create'),
    text: 'New Reservation',
    bgClass: 'bg-blue-600 text-white hover:bg-blue-700'
  }
];

// Handle navigation button clicks
const handleNavigation = (route) => {
  console.log('Navigating to:', route);
  router.get(route, {}, {
    preserveScroll: true,
    onError: (errors) => {
      console.error('Navigation error:', errors);
    }
  });
};

// Table Columns
const columns = [
  {
    accessorKey: 'client.name', // Changed from user.name to client.name
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
    accessorKey: 'price_paid', // Using the correct column name from the database
    header: 'Paid Price',
    cell: ({ row }) => row.original && row.original.price_paid ? `$${row.original.price_paid}` : 'N/A'
  },
  {
    accessorKey: 'check_in_date',
    header: 'Check-in Date',
    cell: ({ row }) => row.original ? formatDate(row.original.check_in_date) : 'N/A'
  },
  {
    accessorKey: 'check_out_date',
    header: 'Check-out Date',
    cell: ({ row }) => row.original ? formatDate(row.original.check_out_date) : 'N/A'
  },
  {
    accessorKey: 'status',
    header: 'Status'
  },
  {
    id: 'actions',
    header: 'Actions'
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

const getStatusVariant = (status) => {
  const variants = {
    'confirmed': 'success',
    'checked_in': 'info',
    'checked-in': 'info',     // Support both formats for backward compatibility
    'checked_out': 'secondary',
    'checked-out': 'secondary', // Support both formats for backward compatibility
    'pending': 'warning',
    'cancelled': 'destructive'
  };
  return variants[status] || 'default';
};

const handlePageChange = (pageIndex) => {
  window.location.href = `/receptionist/reservations?page=${pageIndex + 1}`;
};



const confirmDelete = (reservation) => {
  // Store the reservation object with the correct structure
  selectedReservation.value = reservation.original || reservation;
  showDeleteDialog.value = true;
};

const deleteReservation = () => {
  console.log('Deleting reservation:', selectedReservation.value);
  if (selectedReservation.value) {
    try {
      const id = selectedReservation.value.id;

      // Get CSRF token from cookie if available
      let csrfToken = '';
      const cookies = document.cookie.split(';');
      for (let i = 0; i < cookies.length; i++) {
        const cookie = cookies[i].trim();
        if (cookie.startsWith('XSRF-TOKEN=')) {
          csrfToken = decodeURIComponent(cookie.substring('XSRF-TOKEN='.length));
          break;
        }
      }

      // Prepare headers
      const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      };

      // Add CSRF token if available
      if (csrfToken) {
        headers['X-XSRF-TOKEN'] = csrfToken;
      }

      fetch(`/receptionist/reservations/${id}`, {
        method: 'DELETE',
        headers: headers
      })
      .then(response => {
        if (response.ok) {
          showDeleteDialog.value = false;
          selectedReservation.value = null;
          alert('Reservation deleted successfully!');
          window.location.reload();
        } else {
          console.error('Error deleting reservation, status:', response.status);
          alert('Could not delete reservation. Please try again.');
        }
      })
      .catch(error => {
        console.error('Error deleting reservation:', error);
        alert('Error deleting reservation. Please try again.');
      });
    } catch (error) {
      console.error('Exception in deleteReservation:', error);
      alert('An unexpected error occurred. Please try again.');
    }
  }
};

const approveReservation = async (row) => {
  try {
    // Get the reservation data, handling both possible structures
    let reservation;

    if (row.original) {
      reservation = row.original;
    } else {
      reservation = row;
    }

    if (!reservation || !reservation.id) {
      console.error('Invalid reservation object:', reservation);
      alert('Error: Could not identify reservation. Please try again.');
      return;
    }

    console.log('Approving reservation:', reservation);

    // Create the data object with all required fields
    const data = {
      status: 'confirmed',
      _method: 'PUT' // For method spoofing
    };

    // Add other fields if they exist in the reservation data
    if (reservation.room_id) data.room_id = reservation.room_id;
    if (reservation.accompany_number) data.accompany_number = reservation.accompany_number;
    if (reservation.check_in_date) data.check_in_date = reservation.check_in_date;
    if (reservation.check_out_date) data.check_out_date = reservation.check_out_date;
    if (reservation.price_paid) data.price_paid = reservation.price_paid;
    if (reservation.client_id) data.client_id = reservation.client_id;
    if (reservation.receptionist_id) data.receptionist_id = reservation.receptionist_id;

    console.log('Sending data for approval:', data);

    // First, update the reservation status using axios
    const response = await axios.post(`/receptionist/reservations/${reservation.id}`, data);
    console.log('Reservation approval response:', response.data);

    // Show success message for the reservation
    alert('Reservation approved successfully!');

    // If the client needs to be approved, redirect to the clients page
    if (reservation.client_id) {
      router.visit('/receptionist/clients', {
        onSuccess: () => {
          console.log('Redirected to clients page to approve the client');
        }
      });
    } else {
      // Otherwise, just reload the current page
      router.visit(window.location.pathname, {
        method: 'get',
        preserveScroll: false,
        preserveState: false,
        replace: true,
        onSuccess: () => {
          console.log('Page reloaded after reservation approval');
        }
      });
    }
  } catch (error) {
    console.error('Error approving reservation:', error);
    alert('Could not approve reservation. Please try again.');
  }
};
</script>

<style scoped>
label {
  color: #e5e7eb;
}

:deep(.data-table) {
  --background: #1f2937;
  --text: #e5e7eb;
  --border: #374151;
  --header-background: #111827;
  --header-text: #f9fafb;
  --row-hover: #2d3748;
}

:deep(.data-table th) {
  background-color: #111827;
  color: #f9fafb;
  font-weight: 600;
}

:deep(.data-table td) {
  border-color: #374151;
}

:deep(.data-table tr:hover td) {
  background-color: #2d3748;
}
</style>
