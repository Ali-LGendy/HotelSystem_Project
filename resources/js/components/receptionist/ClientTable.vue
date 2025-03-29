<template>
  <div>
    <div v-if="clients.data.length === 0" class="py-8 text-center">
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
          <tr v-for="client in clients.data" :key="client.id" class="hover:bg-gray-700">
            <td class="whitespace-nowrap px-6 py-4">
              <div class="flex items-center">
                <div class="h-10 w-10 flex-shrink-0">
                  <img
                    v-if="client.avatar_img"
                    :src="getImageUrl(client.avatar_img)"
                    alt="Avatar"
                    class="h-10 w-10 rounded-full border border-gray-600"
                    @error="handleImageError"
                  />
                  <div v-else class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-600">
                    {{ getInitials(client.name) }}
                  </div>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-200">{{ client.name }}</div>
                  <div class="text-sm text-gray-400">{{ client.email }}</div>
                </div>
              </div>
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <div class="text-sm text-gray-300">{{ client.mobile || 'N/A' }}</div>
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <div class="text-sm text-gray-300">{{ client.country || 'N/A' }}</div>
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <span
                :class="[
                  'inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                  client.is_approved
                    ? 'bg-green-900 text-green-200'
                    : 'bg-yellow-900 text-yellow-200'
                ]"
              >
                {{ client.is_approved ? 'Approved' : 'Pending' }}
              </span>
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
              <div class="flex justify-end space-x-2">
                <slot name="actions" :client="client"></slot>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- Pagination -->
    <div v-if="clients.last_page > 1" class="mt-4 flex justify-center">
      <div class="flex space-x-1">
        <button
          v-for="page in clients.last_page"
          :key="page"
          @click="$emit('page-change', page)"
          :class="[
            'rounded-md px-3 py-1 text-sm',
            page === clients.current_page
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
  clients: {
    type: Object,
    required: true
  },
  headers: {
    type: Array,
    default: () => [
      { key: 'name', label: 'Name' },
      { key: 'mobile', label: 'Mobile' },
      { key: 'country', label: 'Country' },
      { key: 'status', label: 'Status' },
      { key: 'actions', label: 'Actions' }
    ]
  },
  emptyMessage: {
    type: String,
    default: 'No clients found.'
  }
});

defineEmits(['page-change']);

const getImageUrl = (path) => {
  if (!path) return '/defaults/user.png';
  
  if (path.startsWith('http')) {
    return path;
  }
  
  return `/storage/${path}`;
};

const handleImageError = (event) => {
  event.target.style.display = 'none';
  const name = event.target.closest('tr').__vnode.key;
  const client = props.clients.data.find(c => c.id === name);
  
  if (client) {
    event.target.parentNode.innerHTML = `
      <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-600">
        ${getInitials(client.name)}
      </div>
    `;
  }
};

const getInitials = (name) => {
  return name
    .split(' ')
    .map((part) => part[0])
    .join('')
    .toUpperCase()
    .substring(0, 2);
};
</script>