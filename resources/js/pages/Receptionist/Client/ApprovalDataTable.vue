<template>
  <div class="rounded-lg bg-gray-900 p-8 text-gray-200 shadow-lg mt-8">
    <h3 class="text-2xl font-bold mb-4 text-gray-100">Approval Data</h3>
    
    <!-- Approval Status -->
    <div class="mb-6 p-4 rounded-lg bg-gray-800">
      <h4 class="text-xl font-semibold mb-2 text-gray-100">Approval Status</h4>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-4 rounded-lg bg-gray-700">
          <div class="text-sm text-gray-400">Total Clients</div>
          <div class="text-2xl font-bold text-gray-100">{{ stats.totalClients }}</div>
        </div>
        <div class="p-4 rounded-lg bg-green-900">
          <div class="text-sm text-gray-300">Approved Clients</div>
          <div class="text-2xl font-bold text-gray-100">{{ stats.approvedClients }}</div>
        </div>
        <div class="p-4 rounded-lg bg-yellow-900">
          <div class="text-sm text-gray-300">Pending Clients</div>
          <div class="text-2xl font-bold text-gray-100">{{ stats.pendingClients }}</div>
        </div>
      </div>
    </div>
    
    <!-- Recent Approvals -->
    <div class="mb-6">
      <h4 class="text-xl font-semibold mb-4 text-gray-100">Recent Approvals</h4>
      <div v-if="recentApprovals.length === 0" class="text-center py-4 bg-gray-800 rounded-lg">
        <p class="text-gray-400">No recent approvals found.</p>
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
                Approved On
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-gray-800 divide-y divide-gray-700">
            <tr v-for="client in recentApprovals" :key="client.id" class="hover:bg-gray-700">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-200">{{ client.name }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-300">{{ client.email }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-300">{{ formatDate(client.approved_at) }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <a
                    :href="`/receptionist/clients/${client.id}/reservations`"
                    class="rounded-md bg-indigo-700 px-3 py-1 text-sm font-medium text-white hover:bg-indigo-600"
                  >
                    View Reservations
                  </a>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    
    <!-- Pending Reservations for Approved Clients -->
    <div>
      <h4 class="text-xl font-semibold mb-4 text-gray-100">Pending Reservations for Approved Clients</h4>
      <div v-if="pendingReservations.length === 0" class="text-center py-4 bg-gray-800 rounded-lg">
        <p class="text-gray-400">No pending reservations found for your approved clients.</p>
      </div>
      <div v-else class="rounded-lg border border-gray-700 bg-gray-800 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-700">
          <thead class="bg-gray-800">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                Client Name
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                Room Number
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                Check-in Date
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                Check-out Date
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-gray-800 divide-y divide-gray-700">
            <tr v-for="reservation in pendingReservations" :key="reservation.id" class="hover:bg-gray-700">
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
                <div class="text-sm text-gray-300">{{ formatDate(reservation.check_in_date) }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-300">{{ formatDate(reservation.check_out_date) }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <button
                    @click="approveReservation(reservation)"
                    class="rounded-md bg-green-700 px-3 py-1 text-sm font-medium text-white hover:bg-green-600"
                  >
                    Approve
                  </button>
                  <a
                    :href="`/receptionist/reservations/${reservation.id}`"
                    class="rounded-md border border-gray-600 bg-gray-700 px-3 py-1 text-sm font-medium text-gray-200 hover:bg-gray-600"
                  >
                    View
                  </a>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

// Props
const props = defineProps({
  stats: {
    type: Object,
    default: () => ({
      totalClients: 0,
      approvedClients: 0,
      pendingClients: 0
    })
  },
  recentApprovals: {
    type: Array,
    default: () => []
  },
  pendingReservations: {
    type: Array,
    default: () => []
  }
});

// For debugging
console.log('ApprovalDataTable props:', {
  stats: props.stats,
  recentApprovals: props.recentApprovals,
  pendingReservations: props.pendingReservations
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

const approveReservation = async (reservation) => {
  if (confirm('Are you sure you want to approve this reservation?')) {
    try {
      // Prepare the data to send
      const data = {
        status: 'confirmed',
        room_id: reservation.room_id,
        client_id: reservation.client_id,
        accompany_number: reservation.accompany_number,
        price_paid: reservation.price_paid,
        _method: 'PUT' // For method spoofing
      };

      // Use axios to make the request
      const response = await axios.post(`/receptionist/reservations/${reservation.id}`, data);
      console.log('Reservation approval response:', response.data);

      // Show success message
      alert('Reservation approved successfully!');

      // Reload the current page
      router.visit(window.location.pathname, {
        method: 'get',
        preserveScroll: false,
        preserveState: false,
        replace: true,
        onSuccess: () => {
          console.log('Page reloaded after reservation approval');
        }
      });
    } catch (error) {
      console.error('Error approving reservation:', error);
      alert('Could not approve reservation due to a technical issue. Please try refreshing the page.');
    }
  }
};
</script>