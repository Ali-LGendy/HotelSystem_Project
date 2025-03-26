<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
      <!-- Header Section with Title and Create Button -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Manage Floors</h1>
        
        <button 
          v-if="isAdmin || permissions?.includes('manage floors')"
          @click="navigateToCreate" 
          class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md flex items-center"
        >
          <span class="mr-1">+</span> Add Floor
        </button>
      </div>
      
      <!-- Alert for messages -->
      <div v-if="successMessage" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">
        <p>{{ successMessage }}</p>
      </div>

      <!-- Table Card -->
      <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div v-if="floors.data.length === 0" class="text-center p-8 text-gray-500">
          No floors found. Create your first floor by clicking the "Add Floor" button above.
        </div>
        
        <table v-else class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
              <th v-if="isAdmin" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Manager</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="floor in floors.data" :key="floor.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ floor.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ floor.number }}</td>
              <td v-if="isAdmin" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ floor.manager_name || 'Not Assigned' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button
                  v-if="floor.can_edit && (!isAdmin || floor.rooms_count === 0 || floor.rooms_count === undefined)"
                  @click="openDeleteDialog(floor)"
                  class="bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded-md mr-2"
                >
                  Delete
                </button>
                <button 
                  v-if="floor.can_edit"
                  @click="navigateToEdit(floor.id)" 
                  class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded-md"
                >
                  Edit
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination Controls -->
        <Pagination :pagination="floors" />
      </div>
    </div>
  </div>

  <!-- Confirmation Dialog -->
  <ConfirmationDialog
    :show="showDeleteDialog"
    title="Delete Floor"
    :message="deleteMessage"
    confirmText="Delete"
    cancelText="Cancel"
    @confirm="executeDelete"
    @cancel="cancelDelete"
  />
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import Pagination from '@/Components/Pagination.vue';
import ConfirmationDialog from '@/Components/ConfirmationDialog.vue';

// Define props
const props = defineProps({
  floors: Object, // Change to Object to accommodate pagination data
  isAdmin: Boolean,
  permissions: Array,
  success: String
});

// Setup reactive state
const successMessage = ref(props.success || '');
const selectedFloor = ref(null);
const showDeleteDialog = ref(false);
const deleteMessage = ref('');
const isLoading = ref(false);

// Log props for debugging
onMounted(() => {
  console.log('Floors:', props.floors);
  console.log('Is Admin:', props.isAdmin);
  console.log('Permissions:', props.permissions);
  
  // Display success message from flash if it exists
  if (props.success) {
    setTimeout(() => {
      successMessage.value = '';
    }, 3000);
  }
});

// Function to navigate to create page
const navigateToCreate = () => {
  router.get(route('floors.create'));
};

// Function to navigate to edit page
const navigateToEdit = (floorId) => {
  router.get(route('floors.edit', floorId));
};

// Function to open delete confirmation dialog
const openDeleteDialog = (floor) => {
  selectedFloor.value = floor;
  deleteMessage.value = `Are you sure you want to delete floor ${floor.name}? This action cannot be undone.`;
  showDeleteDialog.value = true;
};

// Function to cancel deletion
const cancelDelete = () => {
  showDeleteDialog.value = false;
  selectedFloor.value = null;
};

// Function to execute deletion via AJAX
const executeDelete = async () => {
  if (!selectedFloor.value) return;

  isLoading.value = true;

  try {
    // Get the CSRF token from the meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    console.log('CSRF Token:', csrfToken);

    // Create form data with _method=DELETE to handle method spoofing
    const formData = new FormData();
    formData.append('_method', 'DELETE');

    // Make the POST request with _method=DELETE
    await axios.post(route('floors.destroy', selectedFloor.value.id), formData, {
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      }
    });

    // Show success message
    successMessage.value = 'Floor deleted successfully';
    setTimeout(() => {
      successMessage.value = '';
    }, 3000);

    // Refresh the data
    router.reload({ only: ['floors'] });

  } catch (error) {
    // Handle errors
    const errorMessage = error.response?.data?.error || 'An error occurred while deleting the floor';
    alert(errorMessage);
  } finally {
    // Reset state
    isLoading.value = false;
    showDeleteDialog.value = false;
    selectedFloor.value = null;
  }
};

// Pagination is now handled by the Pagination component
</script>
