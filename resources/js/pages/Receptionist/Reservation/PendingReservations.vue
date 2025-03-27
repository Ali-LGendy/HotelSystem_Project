<template>
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="rounded-lg bg-gray-900 p-8 text-gray-200 shadow-lg">
            <!-- Header with Navigation -->
            <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-3xl font-bold">Pending Reservations</h2>
                    <p class="mt-2 text-gray-400">Showing reservations that need your approval</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button
                        @click="navigateTo('/receptionist/all-reservations')"
                        class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
                    >
                        All Reservations
                    </button>
                    <button
                        @click="navigateTo('/receptionist/clients/my-approved')"
                        class="inline-flex items-center justify-center rounded-md bg-secondary px-4 py-2 text-sm font-medium text-secondary-foreground shadow-sm transition-colors hover:bg-secondary/80 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
                    >
                        My Approved Clients
                    </button>
                    <button
                        @click="navigateTo('/receptionist/clients')"
                        class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
                    >
                        Manage Clients
                    </button>
                </div>
            </div>

            <div v-if="reservations.data.length === 0" class="text-center py-8">
                <p class="text-lg text-gray-300">No pending reservations found.</p>
            </div>

            <!-- Data Table -->
            <div v-else class="rounded-lg border border-gray-700 bg-gray-800 overflow-hidden">
                <DataTable
                    :columns="columns"
                    :data="reservations.data"
                    :pagination="{
                        pageSize: perPage,
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
                                class="inline-flex items-center justify-center rounded-md bg-green-600 px-3 py-1 text-xs font-medium text-white shadow-sm transition-colors hover:bg-green-700 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
                                @click.prevent="approveReservation(row)"
                            >
                                Approve
                            </button>
                            <button
                                @click="navigateTo('/receptionist/reservations/' + (row.original ? row.original.id : row.id))"
                                class="inline-flex items-center justify-center rounded-md bg-secondary px-3 py-1 text-xs font-medium text-secondary-foreground shadow-sm transition-colors hover:bg-secondary/80 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
                            >
                                View
                            </button>
                            <button
                                @click="navigateTo('/receptionist/reservations/' + (row.original ? row.original.id : row.id) + '/edit')"
                                class="inline-flex items-center justify-center rounded-md bg-primary px-3 py-1 text-xs font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
                            >
                                Edit
                            </button>
                            <button
                                class="inline-flex items-center justify-center rounded-md bg-destructive px-3 py-1 text-xs font-medium text-destructive-foreground shadow-sm transition-colors hover:bg-destructive/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
                                @click="confirmDelete(row)"
                            >
                                Delete
                            </button>
                        </div>
                    </template>
                </DataTable>

                <!-- Enhanced Pagination -->
                <div v-if="reservations.data.length > 0" class="mt-4 flex justify-between items-center p-4 bg-gray-800">
                    <div class="text-sm text-gray-400">
                        Showing {{ reservations.from }} to {{ reservations.to }} of {{ reservations.total }} reservations
                    </div>

                    <!-- Page Size Selector -->
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center">
                            <span class="text-sm text-gray-400 mr-2">Per page:</span>
                            <select
                                v-model="perPage"
                                @change="sortAndPaginate(1, perPage, currentSort.field, currentSort.direction)"
                                class="h-9 w-20 rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </div>

                        <!-- Page Navigation -->
                        <div class="flex space-x-2">
                            <button
                                v-for="page in reservations.links"
                                :key="page.label"
                                @click="page.url && sortAndPaginate(
                                  page.label === '&laquo; Previous' ? reservations.current_page - 1 :
                                  page.label === 'Next &raquo;' ? reservations.current_page + 1 :
                                  parseInt(page.label),
                                  perPage,
                                  currentSort.field,
                                  currentSort.direction
                                )"
                                :disabled="!page.url"
                                :class="[
                                  'inline-flex items-center justify-center rounded-md px-3 py-1 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50',
                                  page.active
                                    ? 'bg-primary text-primary-foreground shadow hover:bg-primary/90'
                                    : page.url
                                      ? 'border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground'
                                      : 'bg-muted text-muted-foreground cursor-not-allowed'
                                ]"
                                v-html="page.label"
                              ></button>
                        </div>
                    </div>
                </div>  <!-- Closing enhanced pagination div -->
            </div>  <!-- Closing v-else div (Data Table container) -->

            <!-- Delete Confirmation Dialog -->
            <div v-if="showDeleteDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                <div class="w-full max-w-md rounded-lg bg-gray-800 p-6 text-gray-200 shadow-xl">
                    <h3 class="text-xl font-semibold text-gray-100">Confirm Deletion</h3>
                    <p class="mt-2 text-gray-400">
                        Are you sure you want to delete this reservation? This action cannot be undone.
                    </p>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium shadow-sm hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
                            @click="showDeleteDialog = false"
                        >
                            Cancel
                        </button>
                        <button
                            class="inline-flex items-center justify-center rounded-md bg-destructive px-4 py-2 text-sm font-medium text-destructive-foreground shadow-sm hover:bg-destructive/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
                            @click="deleteReservation"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>

            <!-- Reservation Statistics Dashboard -->
            <div class="mt-8 p-6 bg-gray-800 rounded-lg border border-gray-700">
                <h3 class="text-xl font-semibold mb-4 text-gray-100">Reservation Statistics</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
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
                </div>
            </div>
        </div>  <!-- Closing main content div (rounded-lg bg-gray-900) -->
    </div>  <!-- Closing root div (mx-auto max-w-7xl) -->
</template>

<script setup>
import { ref, computed } from 'vue';
import DataTable from '@/components/ui/DataTable.vue';
import { Badge } from '@/components/ui/badge';
import axios from 'axios';
import { router, Link } from '@inertiajs/vue3';
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
      checkedInGuests: 0
    })
  }
});

// State
const showDeleteDialog = ref(false);
const selectedReservation = ref(null);
const currentSort = ref({
  field: 'created_at',
  direction: 'desc'
});
const perPage = ref(10);

// Table Columns
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
  const page = pageIndex + 1;
  sortAndPaginate(page, perPage.value, currentSort.value.field, currentSort.value.direction);
};

// Enhanced pagination with sorting
const sortAndPaginate = (page = 1, perPage = 10, sortBy = 'created_at', sortDir = 'desc') => {
  // Update current sort state
  currentSort.value = {
    field: sortBy,
    direction: sortDir
  };

  // Navigate with new parameters using Inertia's get method
  router.get('/receptionist/reservations', {
    page,
    per_page: perPage,
    sort_by: sortBy,
    sort_dir: sortDir
  }, {
    preserveScroll: true,
    preserveState: true, // Keep component state between requests
    only: ['reservations', 'reservationStats'], // Only refresh these data props
    replace: true // Replace current history entry instead of adding a new one
  });
};

const confirmDelete = (reservation) => {
  // Store the reservation object with the correct structure
  selectedReservation.value = reservation.original || reservation;
  showDeleteDialog.value = true;
};

// Navigation method using Inertia
const navigateTo = (url) => {
  router.get(url, {}, {
    preserveScroll: false,
    preserveState: false,
    replace: false
  });
};

const deleteReservation = () => {
  console.log('Deleting reservation:', selectedReservation.value);
  if (selectedReservation.value) {
    try {
      const id = selectedReservation.value.id;

      router.delete(`/receptionist/reservations/${id}`, {}, {
        onSuccess: () => {
          showDeleteDialog.value = false;
          selectedReservation.value = null;
          alert('Reservation deleted successfully!');
        },
        onError: (errors) => {
          console.error('Error deleting reservation:', errors);
          alert('Could not delete reservation. Please try again.');
        }
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
      status: 'confirmed'
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

    // Use axios directly instead of Inertia to handle the JSON response
    const response = await axios.put(`/receptionist/reservations/${reservation.id}`, data, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });

    // Check if the request was successful
    if (response.data.success) {
      alert('Reservation approved successfully!');

      // Check if client is already approved
      const clientApproved = response.data.client_approved;

      // Refresh the current page to show updated data
      if (reservation.client_id && !clientApproved) {
        // If client is not approved, redirect to clients page
        navigateTo('/receptionist/clients');
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
