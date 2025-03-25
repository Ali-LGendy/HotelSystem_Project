<template>
  <div class="mx-auto max-w-7xl px-4 py-8">
    <div class="rounded-lg bg-gray-900 p-8 text-gray-200 shadow-lg">
      <!-- Header with Navigation -->
      <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h2 class="text-3xl font-bold">All Reservations</h2>
          <p class="mt-2 text-gray-400">Showing all reservations in the system</p>
        </div>
        <div class="flex flex-wrap gap-3">
          <a
            href="/receptionist/reservations"
            class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white transition hover:bg-indigo-700"
          >
            Pending Reservations
          </a>
          <a
            href="/receptionist/clients/pending"
            class="rounded-lg bg-yellow-600 px-4 py-2 font-semibold text-white transition hover:bg-yellow-700"
          >
            Clients Pending Approval
          </a>
          <a
            href="/receptionist/clients/my-approved"
            class="rounded-lg bg-green-600 px-4 py-2 font-semibold text-white transition hover:bg-green-700"
          >
            My Approved Clients
          </a>
          <a
            href="/receptionist/clients/reservations"
            class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white transition hover:bg-indigo-700"
          >
            Clients Reservations
          </a>
          <a
            href="/receptionist/reservations/create"
            class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white transition hover:bg-blue-700"
          >
            New Reservation
          </a>
        </div>
      </div>

      <div v-if="reservations.data.length === 0" class="text-center py-8">
        <p class="text-lg text-gray-300">No pending reservations found.</p>
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
                Room Number
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                Accompany Number
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                Paid Price
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                Check-in Date
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                Check-out Date
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                Status
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-gray-800 divide-y divide-gray-700">
            <tr v-for="reservation in reservations.data" :key="reservation.id" class="hover:bg-gray-700">
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
                <div class="text-sm text-gray-300">{{ reservation.accompany_number }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-300">${{ reservation.price_paid }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-300">{{ formatDate(reservation.check_in_date) }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-300">{{ formatDate(reservation.check_out_date) }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-900 text-yellow-200">
                  {{ reservation.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <button
                    @click="approveReservation(reservation.id)"
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
                  <a
                    :href="`/receptionist/reservations/${reservation.id}/edit`"
                    class="rounded-md border border-gray-600 bg-gray-700 px-3 py-1 text-sm font-medium text-gray-200 hover:bg-gray-600"
                  >
                    Edit
                  </a>
                  <button
                    @click="confirmDelete(reservation.id)"
                    class="rounded-md bg-red-800 px-3 py-1 text-sm font-medium text-white hover:bg-red-700"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="reservations.data.length > 0" class="mt-4 flex justify-between items-center">
        <div class="text-sm text-gray-400">
          Showing {{ reservations.from }} to {{ reservations.to }} of {{ reservations.total }} reservations
        </div>
        <div class="flex space-x-2">
          <button
            v-for="page in reservations.links"
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

      <!-- Delete Confirmation Dialog -->
      <div v-if="showDeleteDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="w-full max-w-md rounded-lg bg-gray-800 p-6 text-gray-200 shadow-xl">
          <h3 class="text-xl font-semibold text-gray-100">Confirm Deletion</h3>
          <p class="mt-2 text-gray-400">
            Are you sure you want to delete this reservation? This action cannot be undone.
          </p>
          <div class="mt-6 flex justify-end space-x-3">
            <button
              class="rounded-md border border-gray-600 bg-gray-700 px-4 py-2 text-sm font-medium text-gray-200 hover:bg-gray-600"
              @click="cancelDelete"
            >
              Cancel
            </button>
            <button
              class="rounded-md bg-red-700 px-4 py-2 text-sm font-medium text-white hover:bg-red-600"
              @click="deleteReservation"
            >
              Delete
            </button>
          </div>
        </div>
      </div>

      <!-- Approve Confirmation Dialog -->
      <div v-if="showApproveDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="w-full max-w-md rounded-lg bg-gray-800 p-6 text-gray-200 shadow-xl">
          <h3 class="text-xl font-semibold text-gray-100">Confirm Approval</h3>
          <p class="mt-2 text-gray-400">
            Are you sure you want to approve this reservation?
          </p>
          <div class="mt-6 flex justify-end space-x-3">
            <button
              class="rounded-md border border-gray-600 bg-gray-700 px-4 py-2 text-sm font-medium text-gray-200 hover:bg-gray-600"
              @click="cancelApprove"
            >
              Cancel
            </button>
            <button
              class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-600"
              @click="confirmApproveReservation"
            >
              Approve
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

// Props
const props = defineProps({
  reservations: {
    type: Object,
    required: true
  }
});

// State
const showDeleteDialog = ref(false);
const showApproveDialog = ref(false);
const selectedReservationId = ref(null);

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

const goToPage = (url) => {
  if (!url) return;
  window.location.href = url;
};

const confirmDelete = (id) => {
  selectedReservationId.value = id;
  showDeleteDialog.value = true;
};

const cancelDelete = () => {
  showDeleteDialog.value = false;
  selectedReservationId.value = null;
};

const deleteReservation = () => {
  if (!selectedReservationId.value) return;
  
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = `/receptionist/reservations/${selectedReservationId.value}`;
  
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const csrfInput = document.createElement('input');
  csrfInput.type = 'hidden';
  csrfInput.name = '_token';
  csrfInput.value = csrfToken;
  
  const methodInput = document.createElement('input');
  methodInput.type = 'hidden';
  methodInput.name = '_method';
  methodInput.value = 'DELETE';
  
  form.appendChild(csrfInput);
  form.appendChild(methodInput);
  document.body.appendChild(form);
  form.submit();
};

const approveReservation = (id) => {
  selectedReservationId.value = id;
  showApproveDialog.value = true;
};

const cancelApprove = () => {
  showApproveDialog.value = false;
  selectedReservationId.value = null;
};

const confirmApproveReservation = () => {
  if (!selectedReservationId.value) return;

  // Find the reservation data
  const reservation = props.reservations.data.find(r => r.id === selectedReservationId.value);
  if (!reservation) return;

  const form = document.createElement('form');
  form.method = 'POST';
  form.action = `/receptionist/reservations/${selectedReservationId.value}`;

  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const csrfInput = document.createElement('input');
  csrfInput.type = 'hidden';
  csrfInput.name = '_token';
  csrfInput.value = csrfToken;

  const methodInput = document.createElement('input');
  methodInput.type = 'hidden';
  methodInput.name = '_method';
  methodInput.value = 'PUT';

  // Add all required fields
  const createInput = (name, value) => {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = name;
    input.value = value;
    return input;
  };

  form.appendChild(csrfInput);
  form.appendChild(methodInput);
  form.appendChild(createInput('status', 'confirmed'));
  form.appendChild(createInput('room_id', reservation.room_id));
  form.appendChild(createInput('accompany_number', reservation.accompany_number));
  form.appendChild(createInput('check_in_date', reservation.check_in_date));
  form.appendChild(createInput('check_out_date', reservation.check_out_date));
  form.appendChild(createInput('price_paid', reservation.price_paid));
  form.appendChild(createInput('client_id', reservation.client_id));

  document.body.appendChild(form);
  form.submit();
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