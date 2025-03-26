<template>
  <div class="mx-auto max-w-7xl px-4 py-8">
    <div class="rounded-lg bg-gray-900 p-8 text-gray-200 shadow-lg">
      <!-- Header with Navigation -->
      <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h2 class="text-3xl font-bold">My Approved Clients</h2>
          <p class="mt-2 text-gray-400">Clients that you have approved</p>
        </div>
        <div class="flex flex-wrap gap-3">
          <button
            @click="navigateTo('/receptionist/clients')"
            class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
          >
            Manage Clients
          </button>
          <button
            @click="navigateTo('/receptionist/clients/reservations')"
            class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
          >
            Clients Reservations
          </button>
          <!-- Pending Reservations button - only visible to admin -->
          <button
            v-if="isAdmin"
            @click="navigateTo('/receptionist/reservations')"
            class="inline-flex items-center justify-center rounded-md bg-secondary px-4 py-2 text-sm font-medium text-secondary-foreground shadow-sm transition-colors hover:bg-secondary/80 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
          >
            Pending Reservations
          </button>
          <!-- Only show All Clients button to admin -->
          <button
            v-if="isAdmin"
            @click="navigateTo('/receptionist/clients/all')"
            class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
          >
            All Clients
          </button>
        </div>
      </div>

      <!-- My Approved Clients Section -->
      <div>
        <div v-if="myApprovedClients.data.length === 0" class="text-center py-8">
          <p class="text-lg text-gray-300">You haven't approved any clients yet.</p>
          <p class="mt-2 text-gray-400">Go to <Link href="/receptionist/clients" class="text-blue-400 hover:underline">Manage Clients</Link> to approve new clients.</p>
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
                  @click="sortAndPaginate(1, 10, 'gender', currentSort.field === 'gender' && currentSort.direction === 'asc' ? 'desc' : 'asc')"
                  scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-700"
                >
                  Gender
                  <span v-if="currentSort.field === 'gender'" class="ml-1">
                    {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                  </span>
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                  {{ isAdmin ? 'Actions' : 'Status' }}
                </th>
              </tr>
            </thead>
            <tbody class="bg-gray-800 divide-y divide-gray-700">
              <tr v-for="client in myApprovedClients.data" :key="client.id" class="hover:bg-gray-700">
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
                  <div class="text-sm text-gray-300">{{ client.gender || 'N/A' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <!-- Status indicator for all roles -->
                    <span
                      :class="client.is_banned ? 'bg-red-900 text-red-200' : 'bg-green-900 text-green-200'"
                      class="px-2 py-1 text-xs font-medium rounded-full"
                    >
                      {{ client.is_banned ? 'Banned' : 'Active' }}
                    </span>

                    <!-- View Reservations button -->
                    <button
                      @click="navigateTo(`/receptionist/clients/${client.id}/reservations`)"
                      class="rounded-md bg-blue-700 px-3 py-1 text-sm font-medium text-white hover:bg-blue-600"
                    >
                      View Reservations
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Enhanced Pagination -->
      <div v-if="myApprovedClients.data.length > 0" class="mt-4 flex justify-between items-center">
        <div class="text-sm text-gray-400">
          Showing {{ myApprovedClients.from }} to {{ myApprovedClients.to }} of {{ myApprovedClients.total }} clients
        </div>

        <!-- Page Size Selector -->
        <div class="flex items-center space-x-4">
          <div class="flex items-center">
            <span class="text-sm text-gray-400 mr-2">Per page:</span>
            <select
              v-model="perPage"
              @change="sortAndPaginate(1, perPage, currentSort.field, currentSort.direction)"
              class="bg-gray-700 text-gray-200 rounded-md px-2 py-1 text-sm"
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
              v-for="page in myApprovedClients.links"
              :key="page.label"
              @click="page.url && sortAndPaginate(
                page.label === '&laquo; Previous' ? myApprovedClients.current_page - 1 :
                page.label === 'Next &raquo;' ? myApprovedClients.current_page + 1 :
                parseInt(page.label),
                perPage,
                currentSort.field,
                currentSort.direction
              )"
              :disabled="!page.url"
              :class="[
                'px-3 py-1 rounded-md text-sm',
                page.active
                  ? 'bg-blue-600 text-white'
                  : page.url
                    ? 'bg-gray-700 text-gray-200 hover:bg-gray-600'
                    : 'bg-gray-800 text-gray-500 cursor-not-allowed'
              ]"
              v-html="page.label"
            ></button>
          </div>
        </div>
      </div>



      <!-- Client Statistics Dashboard -->
      <div class="mt-8 p-6 bg-gray-800 rounded-lg border border-gray-700">
        <h3 class="text-xl font-semibold mb-4 text-gray-100">Client Statistics</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          <div class="p-4 rounded-lg bg-gray-700">
            <div class="text-sm text-gray-400">Total Approved Clients</div>
            <div class="text-2xl font-bold text-gray-100">{{ clientStats.totalApproved }}</div>
          </div>
          <div class="p-4 rounded-lg bg-blue-900">
            <div class="text-sm text-gray-300">Active Reservations</div>
            <div class="text-2xl font-bold text-gray-100">{{ clientStats.activeReservations }}</div>
          </div>
          <div class="p-4 rounded-lg bg-yellow-900">
            <div class="text-sm text-gray-300">Pending Reservations</div>
            <div class="text-2xl font-bold text-gray-100">{{ clientStats.pendingReservations }}</div>
          </div>
        </div>

        <h4 class="text-lg font-semibold mb-3 text-gray-100">Recent Reservations</h4>
        <div v-if="recentReservations.length === 0" class="text-center py-4 bg-gray-700 rounded-lg">
          <p class="text-gray-400">No recent reservations found.</p>
        </div>
        <div v-else class="rounded-lg border border-gray-700 bg-gray-700 overflow-hidden">
          <table class="min-w-full divide-y divide-gray-600">
            <thead class="bg-gray-700">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                  Client
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                  Room
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                  Status
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                  Check-in
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                  Check-out
                </th>
              </tr>
            </thead>
            <tbody class="bg-gray-700 divide-y divide-gray-600">
              <tr v-for="reservation in recentReservations" :key="reservation.id" class="hover:bg-gray-600">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-200">
                    {{ reservation.client ? reservation.client.name : 'N/A' }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-300">
                    {{ reservation.room ? reservation.room.room_number : 'N/A' }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(reservation.status)" class="px-2 py-1 text-xs font-medium rounded-full">
                    {{ reservation.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-300">{{ formatDate(reservation.check_in_date) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-300">{{ formatDate(reservation.check_out_date) }}</div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Confirmation Dialog has been removed -->
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';
import { router, Link } from '@inertiajs/vue3';

// Props
const props = defineProps({
  myApprovedClients: {
    type: Object,
    required: true
  },
  clientStats: {
    type: Object,
    default: () => ({
      totalApproved: 0,
      activeReservations: 0,
      pendingReservations: 0
    })
  },
  recentReservations: {
    type: Array,
    default: () => []
  },
  userRole: {
    type: String,
    default: 'receptionist'
  },
  isAdmin: {
    type: Boolean,
    default: false
  }
});

// State
// Ban-related state variables have been removed
const currentSort = ref({
  field: 'created_at',
  direction: 'desc'
});
const perPage = ref(10);

// No need for co need for computed property anymore, using the prop directly

// Methods
const goToPage = (url) => {
  if (!url) return;
  router.get(url, {}, {
    preserveScroll: true,
    preserveState: true,
    only: ['myApprovedClients', 'clientStats', 'recentReservations']
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
    direction: sortDir
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
    only: ['myApprovedClients', 'clientStats', 'recentReservations'], // Only refresh these data props
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

// Ban/unban methods and related confirmation methods have been removed
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