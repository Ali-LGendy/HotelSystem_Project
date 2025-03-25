<template>
  <div class="mx-auto max-w-7xl px-4 py-8">
    <div class="rounded-lg bg-gray-900 p-8 text-gray-200 shadow-lg">
      <!-- Header with Navigation -->
      <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h2 class="text-3xl font-bold">
            {{ clientId ? 'Client Reservations' : 'Clients Reservations' }}
            <span v-if="clientName" class="text-lg ml-2 text-gray-300">
              ({{ clientName }})
            </span>
          </h2>
          <p class="mt-2 text-gray-400">
            {{ clientId ? `Showing reservations for client: ${clientName}` : 'Showing reservations for all clients approved by you' }}
          </p>
        </div>
        <div class="flex flex-wrap gap-3">
          <a
            href="/receptionist/clients"
            class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white transition hover:bg-blue-700"
          >
            Manage Clients
          </a>
          <a
            href="/receptionist/clients/my-approved"
            class="rounded-lg bg-green-600 px-4 py-2 font-semibold text-white transition hover:bg-green-700"
          >
           My Approved Clients
          </a>
          <a
            href="/receptionist/reservations"
            class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white transition hover:bg-indigo-700"
          >
            Pending Reservations
          </a>
        </div>
      </div>

      <div v-if="clientsReservations.data.length === 0" class="text-center py-8">
        <p class="text-lg text-gray-300">No reservations found for your approved clients.</p>
      </div>

      <!-- Data Table -->
      <div v-else class="rounded-lg border border-gray-700 bg-gray-800 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-700">
          <thead class="bg-gray-800">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                Client Name
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                Accompany Number
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                Room Number
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                Client Paid Price
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-gray-800 divide-y divide-gray-700">
            <tr v-for="reservation in clientsReservations.data" :key="reservation.id" class="hover:bg-gray-700">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-200">
                  {{ reservation.client ? reservation.client.name : 'N/A' }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-300">{{ reservation.accompany_number }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-300">
                  {{ reservation.room ? reservation.room.room_number : 'N/A' }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-300">${{ reservation.price_paid }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <a
                    :href="`/receptionist/reservations/${reservation.id}`"
                    class="rounded-md border border-gray-600 bg-gray-700 px-3 py-1 text-sm font-medium text-gray-200 hover:bg-gray-600"
                  >
                    View
                  </a>
                  <a
                    :href="`/receptionist/reservations/${reservation.id}/edit`"
                    class="rounded-md border border-gray-600 bg-gray-700 px-3 py-1 text-sm font-medium text-gray-200 hover:bg-gray-600"
                  >
                    Edit
                  </a>
                  <button
                    v-if="reservation.status === 'pending'"
                    @click="approveReservation(reservation)"
                    class="rounded-md bg-green-700 px-3 py-1 text-sm font-medium text-white hover:bg-green-600"
                  >
                    Approve
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="clientsReservations.data.length > 0" class="mt-4 flex justify-between items-center">
        <div class="text-sm text-gray-400">
          Showing {{ clientsReservations.from }} to {{ clientsReservations.to }} of {{ clientsReservations.total }} reservations
        </div>
        <div class="flex space-x-2">
          <button
            v-for="page in clientsReservations.links"
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
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

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
        price_paid: reservation.price_paid,
        _method: 'PUT' // For method spoofing
      };

      // Use axios to make the request
      const response = await axios.post(`/receptionist/reservations/${reservation.id}`, data);
      console.log('Reservation approval response:', response.data);

      // Show success message for the reservation
      alert('Reservation approved successfully!');

      // Check if client is already approved from the response
      const clientApproved = response.data.client_approved;

      // If the client is not approved, redirect to the clients page
      if (reservation.client_id && !clientApproved) {
        router.visit('/receptionist/clients', {
          onSuccess: () => {
            console.log('Redirected to clients page to approve the client');
          }
        });
      } else {
        // Otherwise, just reload the current page
        router.visit(window.location.pathname, {
          method: 'get',
          preserveScroll: false,
          preserveState: false,
          replace: true,
          onSuccess: () => {
            console.log('Page reloaded after reservation approval');
          }
        });
      }
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

const goToPage = (url) => {
  if (!url) return;
  router.visit(url, {
    preserveScroll: true,
    preserveState: false
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