<template>
  <div class="mx-auto max-w-7xl px-4 py-8">
    <div class="rounded-lg bg-gray-900 p-8 text-gray-200 shadow-lg">
      <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <NavigationButtons :buttons="navigationButtons" :is-admin="isAdmin" />
      </div>
    </div>
 
    <!-- Pending Clients Section -->
    <div class="mb-8">
      <h3 class="mb-4 text-2xl font-bold text-gray-100">Pending Client Registrations</h3>
      
      <ClientTable 
        :clients="pendingClients" 
        :empty-message="'No pending client registrations found.'"
        @page-change="goToPage(`/receptionist/clients?page=${$event}`)"
      >
        <template #actions="{ client }">
          <button
            @click="openApproveDialog(client)"
            class="rounded-md bg-green-700 px-3 py-1 text-sm font-medium text-white hover:bg-green-600"
          >
            Approve
          </button>
          <button
            @click="openRejectDialog(client)"
            class="rounded-md bg-red-700 px-3 py-1 text-sm font-medium text-white hover:bg-red-600"
          >
            Reject
          </button>
        </template>
      </ClientTable>
    </div>

    <!-- Client Stats Dashboard -->
    <ApprovalDataTable :stats="approvalStats" :recent-approvals="recentApprovals" :pending-reservations="pendingReservations" />
    
    <!-- Confirmation Dialog -->
    <ConfirmDialog
      :show="showConfirmDialog"
      :title="confirmDialogTitle"
      :message="confirmDialogMessage"
      :action-type="confirmAction"
      @confirm="confirmAction === 'approve' ? confirmApprove() : confirmReject()"
      @cancel="cancelConfirmation"
    />
  </div>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { computed } from 'vue';
import ApprovalDataTable from './ApprovalDataTable.vue';
import { useClientManagement } from '@/composables/useClientManagement';
import NavigationButtons from '@/components/receptionist/NavigationButtons.vue';
import ClientTable from '@/components/receptionist/ClientTable.vue';
import ConfirmDialog from '@/components/receptionist/ConfirmDialog.vue';

defineOptions({ layout: AppLayout });

// Props
const props = defineProps({
  pendingClients: {
    type: Object,
    required: true,
  },
  approvedClientsCount: {
    type: Number,
    default: 0,
  },
  myApprovedClientsCount: {
    type: Number,
    default: 0,
  },
  recentlyApprovedClients: {
    type: Array,
    default: () => [],
  },
  pendingReservationsForApprovedClients: {
    type: Array,
    default: () => [],
  },
  currentUserId: {
    type: Number,
    default: null,
  },
  userRole: {
    type: String,
    default: 'receptionist',
  },
  isAdmin: {
    type: Boolean,
    default: false,
  },
});

// Client management functions
const {
  showConfirmDialog,
  confirmDialogTitle,
  confirmDialogMessage,
  confirmAction,
  openApproveDialog,
  openRejectDialog,
  cancelConfirmation,
  confirmApprove,
  confirmReject,
  goToPage
} = useClientManagement();

// Computed properties for ApprovalDataTable
const approvalStats = computed(() => ({
  totalClients: props.pendingClients.total + props.approvedClientsCount,
  approvedClients: props.approvedClientsCount,
  pendingClients: props.pendingClients.total,
}));

const recentApprovals = computed(() => props.recentlyApprovedClients);
const pendingReservations = computed(() => props.pendingReservationsForApprovedClients);

// Navigation buttons configuration
const navigationButtons = [
  {
    path: '/receptionist/clients/my-approved',
    label: 'My Approved Clients',
    primary: false
  },
  {
    path: '/receptionist/clients/reservations',
    label: 'All My Clients Reservations',
    primary: true
  },
  {
    path: '/receptionist/reservations',
    label: 'Pending Reservations',
    primary: false
  },
  {
    path: '/receptionist/clients/all',
    label: 'All Clients',
    primary: true,
    adminOnly: true
  },
  {
    path: '/receptionist/clients/create',
    label: 'Add Client',
    primary: false,
    success: true,
    adminOnly: true
  }
];
</script>