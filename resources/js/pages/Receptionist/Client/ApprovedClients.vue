<template>
  <div class="mx-auto max-w-7xl px-4 py-8">
    <div class="rounded-lg bg-gray-900 p-8 text-gray-200 shadow-lg">
      <!-- Header with Navigation -->
      <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h2 class="text-3xl font-bold">Manage Clients</h2>
          <p class="mt-2 text-gray-400">Approve new clients waiting for approval</p>
        </div>
        <div class="flex flex-wrap gap-3">
          <a
            href="/receptionist/clients/my-approved"
            class="rounded-lg bg-green-600 px-4 py-2 font-semibold text-white transition hover:bg-green-700"
          >
            My Approved Clients
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

      <!-- Pending Clients Section -->
      <div class="mb-8">
        <h3 class="text-2xl font-bold mb-4 text-gray-100">Clients Waiting for Approval</h3>

        <div v-if="pendingClients.data.length === 0" class="text-center py-8">
          <p class="text-lg text-gray-300">No pending client registrations found.</p>
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
              <tr v-for="client in pendingClients.data" :key="client.id" class="hover:bg-gray-700">
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
                    <button
                      @click="approveClient(client.id)"
                      class="rounded-md bg-green-700 px-3 py-1 text-sm font-medium text-white hover:bg-green-600"
                    >
                      Approve
                    </button>
                    <button
                      @click="rejectClient(client.id)"
                      class="rounded-md bg-red-800 px-3 py-1 text-sm font-medium text-white hover:bg-red-700"
                    >
                      Reject
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination for Pending Clients -->
        <div v-if="pendingClients.data.length > 0" class="mt-4 flex justify-between items-center">
          <div class="text-sm text-gray-400">
            Showing {{ pendingClients.from }} to {{ pendingClients.to }} of {{ pendingClients.total }} pending clients
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
              class="rounded-md border border-gray-600 bg-gray-700 px-4 py-2 text-sm font-medium text-gray-200 hover:bg-gray-600"
              @click="cancelConfirmation"
            >
              Cancel
            </button>
            <button
              :class="[
                'rounded-md px-4 py-2 text-sm font-medium text-white',
                confirmAction === 'approve' 
                  ? 'bg-green-700 hover:bg-green-600' 
                  : 'bg-red-700 hover:bg-red-600'
              ]"
              @click="confirmAction === 'approve' ? confirmApprove() : confirmReject()"
            >
              {{ confirmAction === 'approve' ? 'Approve' : 'Reject' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Approval Data Tables -->
      <ApprovalDataTable
        :stats="approvalStats"
        :recent-approvals="recentApprovals"
        :pending-reservations="pendingReservations"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import ApprovalDataTable from './ApprovalDataTable.vue';

// Props
const props = defineProps({
  pendingClients: {
    type: Object,
    required: true
  },
  approvedClientsCount: {
    type: Number,
    default: 0
  },
  recentlyApprovedClients: {
    type: Array,
    default: () => []
  },
  pendingReservationsForApprovedClients: {
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

// Computed properties for ApprovalDataTable
const approvalStats = computed(() => ({
  totalClients: props.pendingClients.total + props.approvedClientsCount,
  approvedClients: props.approvedClientsCount,
  pendingClients: props.pendingClients.total
}));

const recentApprovals = computed(() => props.recentlyApprovedClients);
const pendingReservations = computed(() => props.pendingReservationsForApprovedClients);

// Methods
const goToPage = (url) => {
  if (!url) return;
  router.visit(url, {
    preserveScroll: true,
    preserveState: false
  });
};

const approveClient = (clientId) => {
  selectedClientId.value = clientId;
  confirmAction.value = 'approve';
  confirmDialogTitle.value = 'Confirm Client Approval';
  confirmDialogMessage.value = 'Are you sure you want to approve this client? They will be added to your approved clients list.';
  showConfirmDialog.value = true;
};

const rejectClient = (clientId) => {
  selectedClientId.value = clientId;
  confirmAction.value = 'reject';
  confirmDialogTitle.value = 'Confirm Client Rejection';
  confirmDialogMessage.value = 'Are you sure you want to reject this client? This action cannot be undone.';
  showConfirmDialog.value = true;
};

const cancelConfirmation = () => {
  showConfirmDialog.value = false;
  selectedClientId.value = null;
};

const confirmApprove = async () => {
  if (!selectedClientId.value) return;

  try {
    // Show loading state
    showConfirmDialog.value = false;

    // Use axios to make the request
    const response = await axios.post(`/receptionist/clients/${selectedClientId.value}/approve`);
    console.log('Client approval response:', response.data);

    // If the client was approved successfully, update the data tables
    if (response.data.success) {
      // Add the newly approved client to the recentApprovals
      if (response.data.client) {
        props.recentlyApprovedClients.unshift(response.data.client);
        // Keep only the first 5 items
        if (props.recentlyApprovedClients.length > 5) {
          props.recentlyApprovedClients.pop();
        }
      }

      // Add the updated reservations to pendingReservations
      if (response.data.updatedReservations) {
        props.pendingReservationsForApprovedClients.unshift(...response.data.updatedReservations);
        // Keep only the first 5 items
        if (props.pendingReservationsForApprovedClients.length > 5) {
          props.pendingReservationsForApprovedClients.splice(5);
        }
      }

      // Update the stats
      props.approvedClientsCount++;
    }

    // Use Inertia router to reload the page with fresh data
    router.visit(window.location.pathname, {
      method: 'get',
      preserveScroll: false,
      preserveState: false,
      replace: true,
      onSuccess: () => {
        console.log('Client approved successfully');
      }
    });
  } catch (error) {
    console.error('Error approving client:', error);
    alert('Could not approve client due to a technical issue. Please try refreshing the page.');
  }
};

const confirmReject = async () => {
  if (!selectedClientId.value) return;

  try {
    // Show loading state
    showConfirmDialog.value = false;

    // Use axios to make the request
    await axios.post(`/receptionist/clients/${selectedClientId.value}/reject`);

    // Use Inertia router to reload the page with fresh data
    router.visit(window.location.pathname, {
      method: 'get',
      preserveScroll: false,
      preserveState: false,
      replace: true,
      onSuccess: () => {
        console.log('Client rejected successfully');
      }
    });
  } catch (error) {
    console.error('Error rejecting client:', error);
    alert('Could not reject client due to a technical issue. Please try refreshing the page.');
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