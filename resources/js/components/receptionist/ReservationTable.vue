<template>
  <div>
    <div v-if="reservations.data.length === 0" class="py-8 text-center">
      <p class="text-lg text-gray-300">{{ emptyMessage }}</p>
    </div>
    <div v-else class="overflow-hidden rounded-lg border border-gray-700 bg-gray-800">
      <table class="min-w-full divide-y divide-gray-700">
        <thead class="bg-gray-800">
          <tr>
            <th v-for="header in headers" :key="header.key" scope="col" 
                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">
              {{ header.label }}
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-700 bg-gray-800">
          <tr v-for="reservation in reservations.data" :key="reservation.id" class="hover:bg-gray-700">
            <td class="whitespace-nowrap px-6 py-4">
              <div class="flex items-center">
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-200">
                    {{ reservation.client ? reservation.client.name : 'N/A' }}
                  </div>
                  <div class="text-sm text-gray-400">
                    {{ reservation.client ? reservation.client.email : '' }}
                  </div>
                </div>
              </div>
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <div class="text-sm text-gray-300">
                {{ reservation.room ? `Room ${reservation.room.room_number}` : 'N/A' }}
              </div>
              <div class="text-xs text-gray-400">
                {{ reservation.room ? reservation.room.type : '' }}
              </div>
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <div class="text-sm text-gray-300">{{ formatDate(reservation.check_in_date) }}</div>
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <div class="text-sm text-gray-300">{{ formatDate(reservation.check_out_date) }}</div>
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <span
                :class="[
                  'inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                  getStatusClass(reservation.status)
                ]"
              >
                {{ reservation.status }}
              </span>
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
              <div class="flex justify-end space-x-2">
                <slot name="actions" :reservation="reservation"></slot>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- Pagination -->
    <div v-if="reservations.last_page > 1" class="mt-4 flex justify-center">
      <div class="flex space-x-1">
        <button
          v-for="page in reservations.last_page"
          :key="page"
          @click="$emit('page-change', page)"
          :class="[
            'rounded-md px-3 py-1 text-sm',
            page === reservations.current_page
              ? 'bg-primary text-white'
              : 'bg-gray-700 text-gray-300 hover:bg-gray-600'
          ]"
        >
          {{ page }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  reservations: {
    type: Object,
    required: true
  },
  headers: {
    type: Array,
    default: () => [
      { key: 'client', label: 'Client' },
      { key: 'room', label: 'Room' },
      { key: 'check_in', label: 'Check In' },
      { key: 'check_out', label: 'Check Out' },
      { key: 'status', label: 'Status' },
      { key: 'actions', label: 'Actions' }
    ]
  },
  emptyMessage: {
    type: String,
    default: 'No reservations found.'
  }
});

defineEmits(['page-change']);

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
  switch (status.toLowerCase()) {
    case 'confirmed':
      return 'bg-green-900 text-green-200';
    case 'pending':
      return 'bg-yellow-900 text-yellow-200';
    case 'cancelled':
      return 'bg-red-900 text-red-200';
    case 'completed':
      return 'bg-blue-900 text-blue-200';
    default:
      return 'bg-gray-700 text-gray-300';
  }
};
</script>