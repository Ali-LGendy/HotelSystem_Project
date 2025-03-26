<template>
  <div class="mx-auto max-w-7xl px-4 py-8">
    <div class="rounded-lg bg-gray-900 p-8 text-gray-200 shadow-lg">
      <!-- Header with Navigation -->
      <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h2 class="text-3xl font-bold">
            {{ clientId ? 'Client Reservations' : 'My Approved Clients Reservations' }}
            <span v-if="clientName" class="text-lg ml-2 text-gray-300">
              ({{ clientName }})
            </span>
          </h2>
          <p class="mt-2 text-gray-400">
            {{ clientId ? `Showing all reservations for client: ${clientName}` : 'Showing reservations for all clients approved by you' }}
          </p>
        </div>
        <div class="flex flex-wrap gap-3">
          <button
            @click="navigateTo('/receptionist/clients')"
            class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
          >
            Manage Clients
          </button>
          <button
            @click="navigateTo('/receptionist/clients/my-approved')"
            class="inline-flex items-center justify-center rounded-md bg-secondary px-4 py-2 text-sm font-medium text-secondary-foreground shadow-sm transition-colors hover:bg-secondary/80 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
          >
           My Approved Clients
          </button>
          <button
            @click="navigateTo('/receptionist/reservations')"
            class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
          >
            Pending Reservations
          </button>
        </div>
      </div>

      <div v-if="clientsReservations.data.length === 0" class="text-center py-8">
        <p class="text-lg text-gray-300">No reservations found for your approved clients.</p>
      </div>

      <!-- Data Table -->
      <div v-else class="rounded-lg border border-gray-700 bg-gray-800 overflow-hidden">
        <DataTable
          :columns="columns"
          :data="clientsReservations.data"
          :pagination="{
            pageSize: 10,
            pageIndex: currentPage - 1,
            totalItems: clientsReservations.total,
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

          <!-- Actions Cell Template -->
          <template #cell-actions="{ row }">
            <div class="flex space-x-2">
              <button
                @click="navigateTo(`/receptionist/reservations/${row.id}`)"
                class="inline-flex items-center justify-center rounded-md bg-secondary px-3 py-1 text-xs font-medium text-secondary-foreground shadow-sm transition-colors hover:bg-secondary/80 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
              >
                View
              </button>
              <button
                @click="navigateTo(`/receptionist/reservations/${row.id}/edit`)"
                class="inline-flex items-center justify-center rounded-md bg-primary px-3 py-1 text-xs font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
              >
                Edit
              </button>
              <button
                v-if="row.status === 'pending'"
                @click="approveReservation(row)"
                class="inline-flex items-center justify-center rounded-md bg-green-600 px-3 py-1 text-xs font-medium text-white shadow-sm transition-colors hover:bg-green-700 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
              >
                Approve
              </button>
            </div>
          </template>
        </DataTable>

        <!-- Enhanced Pagination -->
        <div v-if="clientsReservations.data.length > 0" class="mt-4 flex justify-between items-center">
          <div class="text-sm text-gray-400">
            Showing {{ clientsReservations.from }} to {{ clientsReservations.to }} of {{ clientsReservations.total }} reservations
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
                v-for="page in clientsReservations.links"
                :key="page.label"
                @click="page.url && sortAndPaginate(
                  page.label === '&laquo; Previous' ? clientsReservations.current_page - 1 :
                  page.label === 'Next &raquo;' ? clientsReservations.current_page + 1 :
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
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';
import { router, Link } from '@inertiajs/vue3';
import DataTable from '@/components/ui/DataTable.vue';

// Props
const props = defineProps({
  clientsReservations: {
    type: Object,
    required: true
  },
  clientId: {
    type: [Number, String],
    default: null
  },
  clientName: {
    type: String,
    default: null
  }
});

// State
const showApproveDialog = ref(false);
const selectedReservation = ref(null);
const currentSort = ref({
  field: 'created_at',
  direction: 'desc'
});
const perPage = ref(10);

// Table columns definition
const columns = [
  {
    accessorKey: 'client.name',
    header: 'Client Name'
  },
  {
    accessorKey: 'accompany_number',
    header: 'Accompany Number'
  },
  {
    accessorKey: 'room.room_number',
    header: 'Room Number'
  },
  {
    accessorKey: 'price_paid',
    header: 'Client Paid Price'
  },
  {
    id: 'actions',
    header: 'Actions',
    enableSorting: false
  }
];

// Computed
const currentPage = computed(() => {
  return props.clientsReservations.current_page || 1;
});

// Methods
// Enhanced pagination with sorting
const sortAndPaginate = (page = 1, perPage = 10, sortBy = 'created_at', sortDir = 'desc') => {
  // Update current sort state
  currentSort.value = {
    field: sortBy,
    direction: sortDir
  };

  // Prepare parameters
  const params = {
    page,
    per_page: perPage,
    sort_by: sortBy,
    sort_dir: sortDir
  };

  // Add client ID if we're viewing a specific client's reservations
  if (props.clientId) {
    params.id = props.clientId;
  }

  // Navigate with new parameters using Inertia's get method
  router.get(window.location.pathname, params, {
    preserveScroll: true,
    preserveState: true, // Keep component state between requests
    only: ['clientsReservations'], // Only refresh this data prop
    replace: true // Replace current history entry instead of adding a new one
  });
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  try {
    const date = new Date(dateString);
    return date.toLocaleDateString();
  } catch (e) {
    return dateString;
  }
};

const approveReservation = async (reservation) => {
  // Store the selected reservation
  selectedReservation.value = reservation;

  // Show confirmation before submitting
  if (confirm('Are you sure you want to approve this reservation?')) {
    try {
      // First, approve the reservation
      console.log('Approving reservation:', reservation);

      // Prepare the data to send
      const data = {
        status: 'confirmed',
        room_id: reservation.room_id,
        client_id: reservation.client_id,
        accompany_number: reservation.accompany_number,
        price_paid: reservation.price_paid
      };

      // Use Inertia to make the request
      router.put(`/receptionist/reservations/${reservation.id}`, data, {
        onSuccess: (page) => {
          alert('Reservation approved successfully!');

          // Check if client is already approved from the response
          const clientApproved = page.props.flash?.client_approved;

          // If the client is not approved, redirect to the clients page
          if (reservation.client_id && !clientApproved) {
            navigateTo('/receptionist/clients');
          }
        },
        onError: (errors) => {
          console.error('Error approving reservation:', errors);
          alert('Could not approve reservation due to a technical issue. Please try refreshing the page.');
        }
      });
    } catch (error) {
      console.error('Error approving reservation:', error);
      alert('Could not approve reservation due to a technical issue. Please try refreshing the page.');
    }
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
  sortAndPaginate(page, perPage.value, currentSort.value.field, currentSort.value.direction);
};

// Navigation method using Inertia
const navigateTo = (url) => {
  router.get(url, {}, {
    preserveScroll: false,
    preserveState: false,
    replace: false
  });
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