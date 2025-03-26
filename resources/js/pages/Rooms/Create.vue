<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-lg mx-auto bg-white rounded-lg shadow-md p-6">
      <h1 class="text-2xl font-bold mb-6 text-gray-800">Create New Room</h1>

      <!-- Form errors -->
      <div v-if="Object.keys(errors).length > 0" class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
        <div v-for="(error, key) in errors" :key="key" class="text-red-700">
          {{ error }}
        </div>
      </div>

      <form @submit.prevent="submit">
        <div class="mb-4">
          <label for="room_number" class="block text-sm font-medium text-gray-700 mb-1">Room Number</label>
          <input
            id="room_number"
            v-model="form.room_number"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          />
        </div>

        <div class="mb-4">
          <label for="room_capacity" class="block text-sm font-medium text-gray-700 mb-1">Room Capacity</label>
          <input
            id="room_capacity"
            v-model="form.room_capacity"
            type="number"
            min="1"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          />
        </div>

        <div class="mb-4">
          <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">$</span>
            <input
              id="price"
              v-model="form.price"
              type="number"
              min="0"
              step="0.01"
              class="w-full pl-8 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              required
            />
          </div>
        </div>

        <div class="mb-4">
          <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            id="status"
            v-model="form.status"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          >
            <option v-for="status in statuses" :key="status" :value="status">
              {{ status.charAt(0).toUpperCase() + status.slice(1) }}
            </option>
          </select>
        </div>

        <div class="mb-4">
          <label for="floor_id" class="block text-sm font-medium text-gray-700 mb-1">Floor</label>
          <select
            id="floor_id"
            v-model="form.floor_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          >
            <option value="">Select Floor</option>
            <option v-for="floor in floors" :key="floor.id" :value="floor.id">
              {{ floor.name }} ({{ floor.number }})
            </option>
          </select>
        </div>

        <div class="mb-6">
          <label for="manager_id" class="block text-sm font-medium text-gray-700 mb-1">Room Manager</label>
          <select
            id="manager_id"
            v-model="form.manager_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">No Manager</option>
            <option v-for="manager in managers" :key="manager.id" :value="manager.id">
              {{ manager.name }}
            </option>
          </select>
        </div>

        <div class="flex justify-end space-x-3">
          <button
            type="button"
            @click="cancel"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-500"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            Create Room
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  managers: Array,
  floors: Array,
  statuses: Array,
  errors: {
    type: Object,
    default: () => ({})
  }
});

const form = useForm({
  room_number: '',
  room_capacity: '',
  price: '',
  status: 'available',
  floor_id: '',
  manager_id: ''
});

const submit = () => {
  form.post(route('rooms.store'));
};

const cancel = () => {
  form.get(route('rooms.index'));
};
</script>