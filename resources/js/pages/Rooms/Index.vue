<template>
    <div class="min-h-screen bg-background p-8 text-foreground">
        <div class="mx-auto max-w-7xl">
            <!-- Header Section with Title and Create Button -->
            <div class="mb-8 flex items-center justify-between">
                <h1 class="text-3xl font-bold text-foreground">Manage Rooms</h1>
                <Button v-if="isAdmin || permissions?.includes('manage rooms')" @click="navigateToCreate"> + Add Room </Button>
            </div>

            <!-- Alert for messages -->
            <div v-if="successMessage" class="mb-6 rounded-lg border-l-4 border-green-500 bg-green-100 p-4 text-green-700">
                <p>{{ successMessage }}</p>
            </div>

            <!-- ShadCN Table -->
            <Table v-if="rooms?.data?.length > 0" class="w-full rounded-lg border border-border">
                <TableHeader>
                    <TableRow>
                        <TableHead>Room Number</TableHead>
                        <TableHead>Capacity</TableHead>
                        <TableHead>Price</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Floor</TableHead>
                        <TableHead v-if="isAdmin">Manager</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="room in paginatedRooms" :key="room.id" class="transition-colors hover:bg-accent/10">
                        <TableCell class="font-medium">{{ room.room_number }}</TableCell>
                        <TableCell>{{ room.room_capacity }}</TableCell>
                        <TableCell>$ {{ (room.price / 100).toFixed(2) }}</TableCell>
                        <TableCell>
                            <Badge :variant="getStatusVariant(room.status)">
                                {{ room.status }}
                            </Badge>
                        </TableCell>
                        <TableCell>{{ room.floor_name }} ({{ room.floor_number }})</TableCell>
                        <TableCell v-if="isAdmin">{{ room.manager_name || 'Not Assigned' }}</TableCell>
                        <TableCell class="text-right">
                            <div class="flex justify-end space-x-2">
                                <Button
                                    v-if="room.status !== 'occupied' && (isAdmin || room.can_edit)"
                                    @click="openDeleteDialog(room)"
                                    variant="destructive"
                                >
                                    Delete
                                </Button>
                                <Button
                                    v-if="(isAdmin && room.status !== 'occupied') || room.can_edit"
                                    @click="navigateToEdit(room.id)"
                                    variant="outline"
                                >
                                    Edit
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <div v-else class="rounded-lg bg-accent/50 p-8 text-center text-muted-foreground">
                No rooms found. Create your first room by clicking the "Add Room" button above.
            </div>
            <div class="mt-6 flex items-center justify-center gap-2">
                <Link
                    v-for="page in rooms.last_page"
                    :key="page"
                    :href="`?page=${page}`"
                    class="rounded-lg px-4 py-2 transition-colors"
                    :class="{
                        'bg-primary text-primary-foreground': page === rooms.current_page,
                        'bg-secondary text-secondary-foreground hover:bg-secondary/80': page !== rooms.current_page,
                    }"
                >
                    {{ page }}
                </Link>
            </div>
            <!-- Pagination Controls -->
            <!-- <Pagination :currentPage="currentPage" :totalPages="totalPages" @pageChange="handlePageChange" class="mt-6" /> -->
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
import { Link, router } from '@inertiajs/vue3';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmationDialog from '@/components/ConfirmationDialog.vue';
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';
defineOptions({ layout: AppLayout });
const props = defineProps({
    rooms: Object,
    isAdmin: Boolean,
    permissions: Array,
    success: String,
    default: () => ({ data: [] }),
});

const successMessage = ref(props.success || '');
const selectedRoom = ref(null);
const showDeleteDialog = ref(false);
const deleteMessage = ref('');
const isLoading = ref(false);
const currentPage = ref(1);
const itemsPerPage = 10;

const totalPages = computed(() => Math.ceil(props.rooms.total / itemsPerPage));

const paginatedRooms = computed(() => {
    if (!props.rooms?.data) return [];
    const start = (currentPage.value - 1) * itemsPerPage;
    return props.rooms.data.slice(start, start + itemsPerPage);
});

const handlePageChange = (page) => {
    currentPage.value = page;
};

const getStatusVariant = (status) => {
    switch (status) {
        case 'available':
            return 'green';
        case 'occupied':
            return 'secondary';
        case 'maintenance':
            return 'warning';
        default:
            return 'outline';
    }
};

onMounted(() => {
    if (props.success) {
        setTimeout(() => (successMessage.value = ''), 3000);
    }
});

const navigateToCreate = () => router.get(route('rooms.create'));
const navigateToEdit = (roomId) => router.get(route('rooms.edit', roomId));

const openDeleteDialog = (room) => {
    selectedRoom.value = room;
    deleteMessage.value = `Are you sure you want to delete room ${room.room_number}? This action cannot be undone.`;
    showDeleteDialog.value = true;
};

const cancelDelete = () => {
    showDeleteDialog.value = false;
    selectedRoom.value = null;
};

const executeDelete = async () => {
    if (!selectedRoom.value) return;
    isLoading.value = true;

    try {
        console.log('Deleting room:', selectedRoom.value.id);

        // Create form data for the request
        const formData = new FormData();
        formData.append('_method', 'DELETE');

        // Get the CSRF token from the meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            formData.append('_token', csrfToken);
        } else {
            console.warn('CSRF token not found');
        }

        // Log the URL we're sending the request to
        const url = route('rooms.destroy', selectedRoom.value.id);
        console.log('Delete URL:', url);

        // Make the request
        const response = await axios({
            method: 'post',
            url: url,
            data: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });

        console.log('Delete response:', response);

        // Handle successful response
        successMessage.value = 'Room deleted successfully';
        setTimeout(() => (successMessage.value = ''), 3000);

        // Refresh the rooms data
        router.reload({ only: ['rooms'] });
    } catch (error) {
        // Handle error response
        console.error('Delete error details:', {
            message: error.message,
            response: error.response,
            request: error.request,
            config: error.config
        });

        // Extract the error message from the response
        let errorMessage = 'An error occurred while deleting the room';

        if (error.response) {
            console.log('Error response data:', error.response.data);
            console.log('Error response status:', error.response.status);

            // The server responded with an error status
            if (error.response.data && typeof error.response.data === 'object') {
                if (error.response.data.error) {
                    errorMessage = error.response.data.error;
                } else if (error.response.data.message) {
                    errorMessage = error.response.data.message;
                } else {
                    // Try to extract any error message from the response
                    const firstErrorKey = Object.keys(error.response.data)[0];
                    if (firstErrorKey && Array.isArray(error.response.data[firstErrorKey])) {
                        errorMessage = error.response.data[firstErrorKey][0];
                    }
                }
            } else if (typeof error.response.data === 'string') {
                errorMessage = error.response.data;
            }

            // Add status code to error message
            errorMessage += ` (Status: ${error.response.status})`;
        } else if (error.request) {
            // The request was made but no response was received
            errorMessage = 'No response received from server. Please check your network connection.';
        } else {
            // Something happened in setting up the request
            errorMessage = `Request setup error: ${error.message}`;
        }

        alert(errorMessage);
    } finally {
        isLoading.value = false;
        showDeleteDialog.value = false;
        selectedRoom.value = null;
    }
};
</script>
