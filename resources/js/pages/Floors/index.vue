<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
      <!-- Header Section with Title and Create Button -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Manage Floors</h1>
        <Button v-if="isAdmin || permissions?.includes('manage floors')" @click="navigateToCreate">
          + Add Floor
        </Button>
      </div>
      
      <!-- Alert for messages -->
      <div v-if="successMessage" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">
        <p>{{ successMessage }}</p>
      </div>
      
      <!-- ShadCN Table -->
      <Table v-if="floors?.data?.length > 0">
        <TableHeader>
          <TableRow>
            <TableHead>Name</TableHead>
            <TableHead>Number</TableHead>
            <TableHead v-if="isAdmin">Manager</TableHead>
            <TableHead class="text-right">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="floor in paginatedFloors" :key="floor.id">
            <TableCell class="font-medium">{{ floor.name }}</TableCell>
            <TableCell>{{ floor.number }}</TableCell>
            <TableCell v-if="isAdmin">{{ floor.manager_name || 'Not Assigned' }}</TableCell>
            <TableCell class="text-right">
              <Button v-if="floor.can_edit" @click="navigateToEdit(floor.id)" variant="outline">Edit</Button>
              <Button v-if="floor.can_edit && (!isAdmin || !floor.rooms_count)" @click="openDeleteDialog(floor)" variant="destructive" class="ml-2">Delete</Button>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
      
      <div v-else class="text-center p-8 text-gray-500">
        No floors found. Create your first floor by clicking the "Add Floor" button above.
      </div>
      <!-- <ul>
        <li v-for="item in paginatedItems" :key="item">{{ item }}</li>
      </ul> -->
      <!-- Pagination Controls -->
      <Pagination :currentPage="currentPage" :totalPages="totalPages" @pageChange="handlePageChange" />
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
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { Table, TableBody, TableHead, TableHeader, TableRow, TableCell } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import Pagination from '@/components/Pagination.vue';
import ConfirmationDialog from '@/components/ConfirmationDialog.vue';

const props = defineProps({
  floors: Object,
  isAdmin: Boolean,
  permissions: Array,
  success: String,
  default: () => ({ data: [] })
});

const successMessage = ref(props.success || '');
const selectedFloor = ref(null);
const showDeleteDialog = ref(false);
const deleteMessage = ref('');
const isLoading = ref(false);
const currentPage = ref(1);
const itemsPerPage = 10;
const totalItems = ref(50);

const totalPages = computed(() => Math.ceil(props.floors.total / itemsPerPage));

const paginatedItems = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return Array.from({ length: itemsPerPage }, (_, i) => `Item ${start + i + 1}`)
});

const paginatedFloors = computed(() => {
  if (!props.floors?.data) return [];
  const start = (currentPage.value - 1) * itemsPerPage;
  return props.floors.data.slice(start, start + itemsPerPage);
});

const handlePageChange = (page) => {
  currentPage.value = page;
};

onMounted(() => {
  if (props.success) {
    setTimeout(() => successMessage.value = '', 3000);
  }
});

const navigateToCreate = () => router.get(route('floors.create'));
const navigateToEdit = (floorId) => router.get(route('floors.edit', floorId));

const openDeleteDialog = (floor) => {
  selectedFloor.value = floor;
  deleteMessage.value = `Are you sure you want to delete floor ${floor.name}? This action cannot be undone.`;
  showDeleteDialog.value = true;
};

const cancelDelete = () => {
  showDeleteDialog.value = false;
  selectedFloor.value = null;
};

const executeDelete = async () => {
  if (!selectedFloor.value) return;
  isLoading.value = true;
  try {
    await axios.post(route('floors.destroy', selectedFloor.value.id), { _method: 'DELETE' });
    successMessage.value = 'Floor deleted successfully';
    setTimeout(() => successMessage.value = '', 3000);
    router.reload({ only: ['floors'] });
  } catch (error) {
    alert(error.response?.data?.error || 'An error occurred while deleting the floor');
  } finally {
    isLoading.value = false;
    showDeleteDialog.value = false;
    selectedFloor.value = null;
  }
};
</script>