<template>
  <div class="mx-auto max-w-7xl px-4 py-8">
    <div class="rounded-lg bg-gray-900 p-8 text-gray-200 shadow-lg">
      <!-- Header with Navigation -->
      <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h2 class="text-3xl font-bold">All Clients</h2>
          <p class="mt-2 text-gray-400">System-wide view of all clients</p>
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
            @click="navigateTo('/receptionist/clients/reservations')"
            class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
          >
            All My Clients Reservations
          </button>
          <button
            @click="navigateTo('/receptionist/reservations')"
            class="inline-flex items-center justify-center rounded-md bg-secondary px-4 py-2 text-sm font-medium text-secondary-foreground shadow-sm transition-colors hover:bg-secondary/80 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
          >
            Pending Reservations
          </button>
        </div>
      </div>

      <!-- All Clients Section -->
      <div>
        <div v-if="allClients.data.length === 0" class="text-center py-8">
          <p class="text-lg text-gray-300">No clients found in the system.</p>
        </div>

        <div v-else class="rounded-lg border border-gray-700 bg-gray-800 overflow-hidden">
          <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-800">
              <tr>
                <th
                  @click="sortAndPaginate(1, 10, 'name', currentSort.field === 'name' && currentSort.direction === 'asc' ? 'desc' : 'asc')"
                  scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-700"
                >
                  Client Name
                  <span v-if="currentSort.field === 'name'" class="ml-1">
                    {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                  </span>
                </th>
                <th
                  @click="sortAndPaginate(1, 10, 'email', currentSort.field === 'email' && currentSort.direction === 'asc' ? 'desc' : 'asc')"
                  scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-700"
                >
                  Email
                  <span v-if="currentSort.field === 'email'" class="ml-1">
                    {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                  </span>
                </th>
                <th
                  @click="sortAndPaginate(1, 10, 'mobile', currentSort.field === 'mobile' && currentSort.direction === 'asc' ? 'desc' : 'asc')"
                  scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-700"
                >
                  Mobile
                  <span v-if="currentSort.field === 'mobile'" class="ml-1">
                    {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                  </span>
                </th>
                <th
                  @click="sortAndPaginate(1, 10, 'country', currentSort.field === 'country' && currentSort.direction === 'asc' ? 'desc' : 'asc')"
                  scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-700"
                >
                  Country
                  <span v-if="currentSort.field === 'country'" class="ml-1">
                    {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                  </span>
                </th>
                <th
                  @click="sortAndPaginate(1, 10, 'is_approved', currentSort.field === 'is_approved' && currentSort.direction === 'asc' ? 'desc' : 'asc')"
                  scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-700"
                >
                  Status
                  <span v-if="currentSort.field === 'is_approved'" class="ml-1">
                    {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                  </span>
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-gray-800 divide-y divide-gray-700">
              <tr v-for="client in allClients.data" :key="client.id" class="hover:bg-gray-700">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-200">{{ client.name }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-300">{{ client.email }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-300">{{ client.mobile || 'N/A' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-300">{{ client.country || 'N/A' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span 
                    :class="getStatusClass(client)"
                    class="px-2 py-1 text-xs font-medium rounded-full"
                  >
                    {{ getStatusText(client) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <!-- View Reservations button for all roles -->
                    <button
                      @click="navigateTo(`/receptionist/clients/${client.id}/reservations`)"
                      class="inline-flex items-center justify-center rounded-md bg-secondary px-3 py-1 text-xs font-medium text-secondary-foreground shadow-sm transition-colors hover:bg-secondary/80 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
                    >
                      View Reservations
                    </button>

                    <!-- Edit/Delete Actions (Admin Only) -->
                    <button
                      v-if="isAdmin"
                      @click="editClient(client.id)"
                      class="inline-flex items-center justify-center rounded-md bg-primary px-3 py-1 text-xs font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
                    >
                      Edit
                    </button>
                    <button
                      v-if="isAdmin"
                      @click="deleteClient(client.id)"
                      class="inline-flex items-center justify-center rounded-md bg-destructive px-3 py-1 text-xs font-medium text-destructive-foreground shadow-sm transition-colors hover:bg-destructive/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
                    >
                        Pending Reservations
                    </a>
                </div>
            </div>

            <!-- All Clients Section -->
            <div>
                <div v-if="allClients.data.length === 0" class="py-8 text-center">
                    <p class="text-lg text-gray-300">No clients found in the system.</p>
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
              v-for="page in allClients.links"
              :key="page.label"
              @click="page.url && sortAndPaginate(
                page.label === '&laquo; Previous' ? allClients.current_page - 1 :
                page.label === 'Next &raquo;' ? allClients.current_page + 1 :
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

                <!-- Page Size Selector -->
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <span class="mr-2 text-sm text-gray-400">Per page:</span>
                        <select
                            v-model="perPage"
                            @change="sortAndPaginate(1, perPage, currentSort.field, currentSort.direction)"
                            class="rounded-md bg-gray-700 px-2 py-1 text-sm text-gray-200"
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
                            v-for="page in allClients.links"
                            :key="page.label"
                            @click="
                                page.url &&
                                sortAndPaginate(
                                    page.label === '&laquo; Previous'
                                        ? allClients.current_page - 1
                                        : page.label === 'Next &raquo;'
                                          ? allClients.current_page + 1
                                          : parseInt(page.label),
                                    perPage,
                                    currentSort.field,
                                    currentSort.direction,
                                )
                            "
                            :disabled="!page.url"
                            :class="[
                                'rounded-md px-3 py-1 text-sm',
                                page.active
                                    ? 'bg-blue-600 text-white'
                                    : page.url
                                      ? 'bg-gray-700 text-gray-200 hover:bg-gray-600'
                                      : 'cursor-not-allowed bg-gray-800 text-gray-500',
                            ]"
                            v-html="page.label"
                        ></button>
                    </div>
                </div>
            </div>

      <!-- Confirmation Dialog -->
      <div v-if="showConfirmDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="w-full max-w-md rounded-lg bg-gray-800 p-6 text-gray-200 shadow-xl">
          <h3 class="text-xl font-semibold text-gray-100">{{ confirmDialogTitle }}</h3>
          <p class="mt-2 text-gray-400">{{ confirmDialogMessage }}</p>
          <div class="mt-6 flex justify-end space-x-3">
            <button
              class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium shadow-sm hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
              @click="cancelConfirmation"
            >
              Cancel
            </button>
            <button
              class="inline-flex items-center justify-center rounded-md bg-destructive px-4 py-2 text-sm font-medium text-destructive-foreground shadow-sm hover:bg-destructive/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
              @click="confirmDelete()"
            >
              Delete
            </button>
          </div>
        </div>
    </div>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import axios from 'axios';
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
defineOptions({ layout: AppLayout });

// Props
const props = defineProps({
    allClients: {
        type: Object,
        required: true,
    },
    clientStats: {
        type: Object,
        default: () => ({
            totalClients: 0,
            approvedClients: 0,
            pendingClients: 0,
        }),
    },
    recentReservations: {
        type: Array,
        default: () => [],
    },
    userRole: {
        type: String,
        default: 'receptionist',
    },
    isAdmin: {
        type: Boolean,
        default: false,
    },
    currentUserId: {
        type: Number,
        required: true,
    },
    debug: {
        type: Object,
        default: null,
    },
});

// State
const showConfirmDialog = ref(false);
const confirmDialogTitle = ref('');
const confirmDialogMessage = ref('');
const selectedClientId = ref(null);
const currentSort = ref({
    field: 'created_at',
    direction: 'desc',
});
const perPage = ref(10);

// Methods
const goToPage = (url) => {
  if (!url) return;

  // Extract the query parameters from the URL
  const urlObj = new URL(url, window.location.origin);
  const params = Object.fromEntries(urlObj.searchParams.entries());

  // Use Inertia's get method with the extracted parameters
  router.get(urlObj.pathname, params, {
    preserveScroll: true,
    preserveState: true,
    only: ['allClients', 'clientStats', 'recentReservations']
  });
};

// Navigation method using Inertia
const navigateTo = (url) => {
  router.get(url, {}, {
    preserveScroll: false,
    preserveState: false,
    replace: false
  });
};

// Enhanced pagination with sorting
const sortAndPaginate = (page = 1, perPage = 10, sortBy = 'created_at', sortDir = 'desc') => {
    // Update current sort state
    currentSort.value = {
        field: sortBy,
        direction: sortDir,
    };

  // Navigate with new parameters using Inertia's get method
  router.get(window.location.pathname, {
    page,
    per_page: perPage,
    sort_by: sortBy,
    sort_dir: sortDir
  }, {
    preserveScroll: true,
    preserveState: true, // Keep component state between requests
    only: ['allClients', 'clientStats', 'recentReservations'], // Only refresh these data props
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

const getReservationStatusClass = (status) => {
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

const getStatusClass = (client) => {
    if (client.is_approved) {
        return 'bg-green-900 text-green-200';
    } else {
        return 'bg-yellow-900 text-yellow-200';
    }
};

const getStatusText = (client) => {
  return client.is_approved ? 'Approved' : 'Pending';
};

const cancelConfirmation = () => {
    showConfirmDialog.value = false;
    selectedClientId.value = null;
};

// Edit client method
const editClient = (clientId) => {
  // Navigate to the edit page using Inertia
  router.get(`/receptionist/clients/${clientId}/edit`, {}, {
    preserveState: false // Don't preserve state when navigating to a different page
  });
};

// Delete client method
const deleteClient = (clientId) => {
    selectedClientId.value = clientId;
    confirmDialogTitle.value = 'Confirm Client Deletion';
    confirmDialogMessage.value =
        'Are you sure you want to delete this client? This action cannot be undone and will remove all associated reservations.';
    showConfirmDialog.value = true;
};

// Confirm delete method
const confirmDelete = () => {
  if (!selectedClientId.value) return;

  // Hide dialog first
  showConfirmDialog.value = false;

  // Use Inertia's delete method
  router.delete(`/receptionist/clients/${selectedClientId.value}`, {}, {
    onSuccess: () => {
      // Success is handled automatically by Inertia refreshing the page data
      selectedClientId.value = null;
    },
    onError: (errors) => {
      console.error('Error deleting client:', errors);
      alert('Could not delete client due to a technical issue. Please try again.');
    },
    preserveScroll: true
  });
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
