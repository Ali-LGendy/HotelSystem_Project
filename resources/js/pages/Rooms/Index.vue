<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
      <!-- Header Section with Title and Create Button -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Manage Rooms</h1>

        <button
          v-if="isAdmin || permissions?.includes('manage rooms')"
          @click="navigateToCreate"
          class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md flex items-center"
        >
          <span class="mr-1">+</span> Add Room
        </button>
      </div>

      <!-- Alert for messages -->
      <div v-if="successMessage" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">
        <p>{{ successMessage }}</p>
      </div>

      <!-- Table Card -->
      <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div v-if="rooms.data.length === 0" class="text-center p-8 text-gray-500">
          No rooms found. Create your first room by clicking the "Add Room" button above.
        </div>

        <table v-else class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room Number</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacity</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Floor</th>
              <th v-if="isAdmin" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Manager</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="room in rooms.data" :key="room.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ room.room_number }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ room.room_capacity }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ room.price }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span :class="getStatusClass(room.status)" class="px-2 py-1 rounded-full text-xs font-medium">
                  {{ room.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ room.floor_name }} ({{ room.floor_number }})
              </td>
              <td v-if="isAdmin" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ room.manager_name || 'Not Assigned' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <!-- Delete Button: Show if NOT occupied -->
                <button
                  v-if="room.status !== 'occupied' && ((isAdmin) || (room.can_edit))"
                  @click="openDeleteDialog(room)"
                  class="bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded-md mr-2"
                >
                  Delete
                </button>
                <!-- Edit Button: Show for admin (if not occupied) OR if manager owns the room -->
                <button
                  v-if="(isAdmin && room.status !== 'occupied') || (room.can_edit)"
                  @click="navigateToEdit(room.id)"
                  class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded-md"
                >
                  Edit
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination Controls -->
        <Pagination :pagination="rooms" />
      </div>
    </div>
  </div>

  <!-- Confirmation Dialog -->
  <ConfirmationDialog
    :show="showDeleteDialog"
    title="Delete Room"
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
  rooms: Object,
  isAdmin: Boolean,
  isManager: Boolean,
  permissions: Array,
  success: String
});

// Setup reactive state
const successMessage = ref(props.success || '');
const selectedRoom = ref(null);
const showDeleteDialog = ref(false);
const deleteMessage = ref('');
const isLoading = ref(false);

// Log props for debugging
onMounted(() => {
  console.log('Rooms:', props.rooms);
  console.log('Is Admin:', props.isAdmin);
  console.log('Is Manager:', props.isManager);
  console.log('Permissions:', props.permissions);

  // Display success message from flash if it exists
  if (props.success) {
    setTimeout(() => {
      successMessage.value = '';
    }, 3000);
  }
});

// Function to get status class based on status
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

// Function to navigate to create page
const navigateToCreate = () => {
  router.get(route('rooms.create'));
};

// Function to navigate to edit page
const navigateToEdit = (roomId) => {
  router.get(route('rooms.edit', roomId));
};

// Function to open delete confirmation dialog
const openDeleteDialog = (room) => {
  selectedRoom.value = room;
  deleteMessage.value = `Are you sure you want to delete room ${room.room_number}? This action cannot be undone.`;
  showDeleteDialog.value = true;
};

// Function to cancel deletion
const cancelDelete = () => {
  showDeleteDialog.value = false;
  selectedRoom.value = null;
};

// Function to execute deletion via AJAX
const executeDelete = async () => {
  if (!selectedRoom.value) return;

  isLoading.value = true;

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const formData = new FormData();
    formData.append('_method', 'DELETE');

    const response = await axios.post(route('rooms.destroy', selectedRoom.value.id), formData, {
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      }
    });

    // Success handling
    successMessage.value = response.data.success || 'Room deleted successfully';
    setTimeout(() => {
      successMessage.value = '';
    }, 3000);

    // Refresh the data
    router.reload({ only: ['rooms'] });

  } catch (error) {
    // Improved error handling
    const errorMessage = error.response?.data?.error || error.response?.data?.message || 'An error occurred while deleting the room';
    
    // Use a more user-friendly error display
    alert(errorMessage);
    
    // Optional: log detailed error for debugging
    console.error('Delete error:', error.response);
  } finally {
    // Reset state
    isLoading.value = false;
    showDeleteDialog.value = false;
    selectedRoom.value = null;
  }
};
</script>