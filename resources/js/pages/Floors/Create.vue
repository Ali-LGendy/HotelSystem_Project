<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-lg mx-auto bg-white rounded-lg shadow-md p-6">
      <h1 class="text-2xl font-bold mb-6 text-gray-800">Create New Floor</h1>
      
      <!-- Form errors -->
      <div v-if="Object.keys(errors).length > 0" class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
        <div v-for="(error, key) in errors" :key="key" class="text-red-700">
          {{ error }}
        </div>
      </div>
      
      <form @submit.prevent="submit">
        <div class="mb-4">
          <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Floor Name</label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            required
          />
        </div>
        
        <div class="mb-6">
          <label for="manager_id" class="block text-sm font-medium text-gray-700 mb-1">Floor Manager</label>
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
            Create Floor
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    floor: Object, // Keep this if you use it
    managers: Array,
    errors: { // Define errors as a prop
        type: Object,
        default: () => ({}) // Default to an empty object
    }
});

const form = useForm({
    name: props.floor?.name || '', // Use optional chaining
    number: props.floor?.number || '',
    manager_id: props.floor?.manager_id || ''
});

const submit = () => {
  form.post(route('floors.store'));
};

const cancel = () => {
  router.visit(route('floors.index'), {
    method: 'get',
    preserveState: false,
    preserveScroll: false,
    only: []
  });
};
</script>