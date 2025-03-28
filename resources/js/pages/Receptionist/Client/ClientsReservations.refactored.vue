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
        <NavigationButtons :buttons="navigationButtons" :is-admin="isAdmin" />
      </div>

      <!-- Reservation Table -->
      <ReservationTable 
        :reservations="clientsReservations" 
        :headers="tableHeaders"
        :empty-message="'No reservations found for your approved clients.'"
        @page-change="goToPage(`/receptionist/clients/reservations${clientId ? '/' + clientId : ''}?page=${$event}`)"
      >
        <template #actions="{ reservation }">
          <button
            v-if="reservation.status === 'pending'"
            @click="approveReservation(reservation.id)"
            class="rounded-md bg-green-700 px-3 py-1 text-sm font-medium text-white hover:bg-green-600"
            :disabled="processing"
          >
            Approve
          </button>
          <button
            v-if="['pending', 'confirmed'].includes(reservation.status)"
            @click="cancelReservation(reservation.id)"
            class="rounded-md bg-red-700 px-3 py-1 text-sm font-medium text-white hover:bg-red-600"
            :disabled="processing"
          >
            Cancel
          </button>
          <button
            v-if="reservation.status === 'confirmed'"
            @click="completeReservation(reservation.id)"
            class="rounded-md bg-blue-700 px-3 py-1 text-sm font-medium text-white hover:bg-blue-600"
            :disabled="processing"
          >
            Complete
          </button>
          <button
            @click="viewReservationDetails(reservation.id)"
            class="rounded-md border border-gray-600 bg-gray-700 px-3 py-1 text-sm font-medium text-gray-200 hover:bg-gray-600"
          >
            View
          </button>
        </template>
      </ReservationTable>
    </div>
  </div>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { useReservationManagement } from '@/composables/useReservationManagement';
import NavigationButtons from '@/components/receptionist/NavigationButtons.vue';
import ReservationTable from '@/components/receptionist/ReservationTable.vue';

defineOptions({ layout: AppLayout });

// Props
const props = defineProps({
  clientsReservations: {
    type: Object,
    required: true,
  },
  clientId: {
    type: [Number, String],
    default: null,
  },
  clientName: {
    type: String,
    default: '',
  },
  isAdmin: {
    type: Boolean,
    default: false,
  },
});

// Reservation management functions
const {
  processing,
  approveReservation,
  cancelReservation,
  completeReservation,
  goToPage,
  viewReservationDetails
} = useReservationManagement();

// Table headers
const tableHeaders = [
  { key: 'client', label: 'Client' },
  { key: 'room', label: 'Room' },
  { key: 'check_in', label: 'Check In' },
  { key: 'check_out', label: 'Check Out' },
  { key: 'status', label: 'Status' },
  { key: 'actions', label: 'Actions' }
];

// Navigation buttons configuration
const navigationButtons = [
  {
    path: '/receptionist/clients',
    label: 'Manage Clients',
    primary: true
  },
  {
    path: '/receptionist/clients/my-approved',
    label: 'My Approved Clients',
    primary: false
  },
  {
    path: '/receptionist/reservations',
    label: 'Pending Reservations',
    primary: true
  }
];
</script>