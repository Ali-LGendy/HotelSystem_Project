<template>
  <div class="mx-auto max-w-7xl px-4 py-8">
    <div class="rounded-lg bg-gray-900 p-8 text-gray-200 shadow-lg">
      <!-- Header with Navigation -->
      <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h2 class="text-3xl font-bold">Clients Pending Approval</h2>
          <p class="mt-2 text-gray-400">Manage client registration requests</p>
        </div>
        <NavigationButtons :buttons="navigationButtons" :is-admin="isAdmin" />
      </div>

      <!-- Client Table -->
      <ClientTable 
        :clients="pendingClients" 
        :headers="tableHeaders"
        :empty-message="'No pending client registrations found.'"
        @page-change="goToPage(`/receptionist/clients/pending?page=${$event}`)"
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

// Table headers
const tableHeaders = [
  { key: 'name', label: 'Client Name' },
  { key: 'email', label: 'Email' },
  { key: 'mobile', label: 'Mobile' },
  { key: 'country', label: 'Country' },
  { key: 'gender', label: 'Gender' },
  { key: 'actions', label: 'Actions' }
];

// Navigation buttons configuration
const navigationButtons = [
  {
    path: '/receptionist/clients/my-approved',
    label: 'My Approved Clients',
    primary: true
  },
  {
    path: '/receptionist/clients/reservations',
    label: 'Clients Reservations',
    primary: false
  }
];
</script>