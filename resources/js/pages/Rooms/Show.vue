<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
      <div class="p-6">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-bold text-gray-800">Room Details</h1>
          <div class="flex space-x-2">
            <button
              @click="navigateToEdit"
              class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md"
            >
              Edit
            </button>
            <button
              @click="navigateToIndex"
              class="bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 px-4 rounded-md"
            >
              Back to List
            </button>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="bg-gray-50 p-4 rounded-lg">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Room Information</h2>
            
            <div class="mb-4">
              <p class="text-sm text-gray-500">Room Number</p>
              <p class="text-lg font-medium">{{ room.room_number }}</p>
            </div>
            
            <div class="mb-4">
              <p class="text-sm text-gray-500">Capacity</p>
              <p class="text-lg font-medium">{{ room.room_capacity }} person(s)</p>
            </div>
            
            <div class="mb-4">
              <p class="text-sm text-gray-500">Price</p>
              <p class="text-lg font-medium">${{ room.price }}</p>
            </div>
            
            <div class="mb-4">
              <p class="text-sm text-gray-500">Status</p>
              <span :class="getStatusClass(room.status)" class="px-3 py-1 rounded-full text-sm font-medium">
                {{ room.status }}
              </span>
            </div>
          </div>
          
          <div class="bg-gray-50 p-4 rounded-lg">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Location & Management</h2>
            
            <div class="mb-4" v-if="room.floor">
              <p class="text-sm text-gray-500">Floor</p>
              <p class="text-lg font-medium">{{ room.floor.name }} ({{ room.floor.number }})</p>
            </div>
            
            <div class="mb-4" v-if="room.manager">
              <p class="text-sm text-gray-500">Manager</p>
              <p class="text-lg font-medium">{{ room.manager.name }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
  room: Object
});

const getStatusClass = (status) => {
  switch (status) {
    case 'available':
      return 'bg-green-100 text-green-800';
    case 'occupied':
      return 'bg-blue-100 text-blue-800';
    case 'maintenance':
      return 'bg-yellow-100 text-yellow-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
};

const navigateToEdit = () => {
  router.get(route('rooms.edit', props.room.id));
};

const navigateToIndex = () => {
  router.get(route('rooms.index'));
};
</script>