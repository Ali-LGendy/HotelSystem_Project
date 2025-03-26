<template>
  <div>
    <ApprovedClients
      :pendingClients="pendingClients"
      :approvedClientsCount="approvedClientsCount"
      :myApprovedClientsCount="myApprovedClientsCount"
      :recentlyApprovedClients="recentlyApprovedClients"
      :pendingReservationsForApprovedClients="pendingReservationsForApprovedClients"
      :currentUserId="currentUserId"
      :userRole="userRole"
      :isAdmin="isAdmin"
    />
  </div>
</template>

<script setup>
import { defineProps } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ApprovedClients from './ApprovedClients.vue';

// Props passed from the controller
const props = defineProps({
  pendingClients: {
    type: Object,
    required: true
  },
  approvedClientsCount: {
    type: Number,
    default: 0
  },
  myApprovedClientsCount: {
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
  },
  currentUserId: {
    type: Number,
    default: null
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

defineOptions({ layout: AppLayout });

</script>