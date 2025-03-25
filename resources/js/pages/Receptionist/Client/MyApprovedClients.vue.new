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
          <a
            href="/receptionist/clients"
            class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white transition hover:bg-blue-700"
          >
            Manage Clients
          </a>
          <a
            href="/receptionist/clients/reservations"
            class="rounded-lg bg-purple-600 px-4 py-2 font-semibold text-white transition hover:bg-purple-700"
          >
            Clients Reservations
          </a>
          <a
            href="/receptionist/reservations"
            class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white transition hover:bg-indigo-700"
          >
            Pending Reservations
          </a>
        </div>
      </div>

      <!-- My Approved Clients Section -->
      <div>
        <div v-if="myApprovedClients.data.length === 0" class="text-center py-8">
          <p class="text-lg text-gray-300">You haven't approved any clients yet.</p>
        </div>

        <div v-else class="rounded-lg border border-gray-700 bg-gray-800 overflow-hidden">
          <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-800">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                  Client Name
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                  Email
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                  Mobile
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                  Country
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                  Gender
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                  Actions
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
                    <a
                      :href="`/receptionist/clients/${client.id}/reservations`"
                      class="rounded-md bg-indigo-700 px-3 py-1 text-sm font-medium text-white hover:bg-indigo-600"
                    >
                      View Reservations
                    </a>
                    <button
                      v-if="!client.is_banned"
                      @click="banClient(client.id)"
                      class="rounded-md bg-red-800 px-3 py-1 text-sm font-medium text-white hover:bg-red-700"
                    >
                      Ban
                    </button>
                    <button
                      v-else
                      @click="unbanClient(client.id)"
                      class="rounded-md bg-green-700 px-3 py-1 text-sm font-medium text-white hover:bg-green-600"
                    >
                      Unban
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="myApprovedClients.data.length > 0" class="mt-4 flex justify-between items-center">
        <div class="text-sm text-gray-400">
          Showing {{ myApprovedClients.from }} to {{ myApprovedClients.to }} of {{ myApprovedClients.total }} clients
        </div>
        <div class="flex space-x-2">
          <button
            v-for="page in myApprovedClients.links"
            :key="page.label"
            @click="goToPage(page.url)"
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

      <!-- Confirmation Dialog -->
      <div v-if="showConfirmDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="w-full max-w-md rounded-lg bg-gray-800 p-6 text-gray-200 shadow-xl">
          <h3 class="text-xl font-semibold text-gray-100">{{ confirmDialogTitle }}</h3>
          <p class="mt-2 text-gray-400">{{ confirmDialogMessage }}</p>
          <div class="mt-6 flex justify-end space-x-3">
            <button
              class="rounded-md border border-gray-600 bg-gray-700 px-4 py-2 text-sm font-medium text-gray-200 hover:bg-gray-600"
              @click="cancelConfirmation"
            >
              Cancel
            </button>
            <button
              :class="[
                'rounded-md px-4 py-2 text-sm font-medium text-white',
                confirmAction === 'ban' 
                  ? 'bg-red-700 hover:bg-red-600' 
                  : 'bg-green-700 hover:bg-green-600'
              ]"
              @click="confirmAction === 'ban' ? confirmBan() : confirmUnban()"
            >
              {{ confirmAction === 'ban' ? 'Ban' : 'Unban' }}
            </button>
          </div>
        </div>
      </div>
      
      <!-- Client Statistics Dashboard -->
      <ClientStatsDashboard 
        :stats="clientStats" 
        :reservations="recentReservations" 
      />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import ClientStatsDashboard from './ClientStatsDashboard.vue';

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
  }
});

// State
const showConfirmDialog = ref(false);
const confirmDialogTitle = ref('');
const confirmDialogMessage = ref('');
const confirmAction = ref('');
const selectedClientId = ref(null);

// Methods
const goToPage = (url) => {
  if (!url) return;
  router.visit(url, {
    preserveScroll: true,
    preserveState: false
  });
};

const banClient = (clientId) => {
  selectedClientId.value = clientId;
  confirmAction.value = 'ban';
  confirmDialogTitle.value = 'Confirm Client Ban';
  confirmDialogMessage.value = 'Are you sure you want to ban this client? They will not be able to make new reservations.';
  showConfirmDialog.value = true;
};

const unbanClient = (clientId) => {
  selectedClientId.value = clientId;
  confirmAction.value = 'unban';
  confirmDialogTitle.value = 'Confirm Client Unban';
  confirmDialogMessage.value = 'Are you sure you want to unban this client? They will be able to make reservations again.';
  showConfirmDialog.value = true;
};

const cancelConfirmation = () => {
  showConfirmDialog.value = false;
  selectedClientId.value = null;
};

const confirmBan = async () => {
  if (!selectedClientId.value) return;

  try {
    // Show loading state
    showConfirmDialog.value = false;

    // Use axios to make the request
    await axios.post(`/receptionist/clients/${selectedClientId.value}/ban`);

    // Use Inertia router to reload the page with fresh data
    router.visit(window.location.pathname, {
      method: 'get',
      preserveScroll: false,
      preserveState: false,
      replace: true,
      onSuccess: () => {
        console.log('Client banned successfully');
      }
    });
  } catch (error) {
    console.error('Error banning client:', error);
    alert('Could not ban client due to a technical issue. Please try refreshing the page.');
  }
};

const confirmUnban = async () => {
  if (!selectedClientId.value) return;

  try {
    // Show loading state
    showConfirmDialog.value = false;

    // Use axios to make the request
    await axios.post(`/receptionist/clients/${selectedClientId.value}/unban`);

    // Use Inertia router to reload the page with fresh data
    router.visit(window.location.pathname, {
      method: 'get',
      preserveScroll: false,
      preserveState: false,
      replace: true,
      onSuccess: () => {
        console.log('Client unbanned successfully');
      }
    });
  } catch (error) {
    console.error('Error unbanning client:', error);
    alert('Could not unban client due to a technical issue. Please try refreshing the page.');
  }
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