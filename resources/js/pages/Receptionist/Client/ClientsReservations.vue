<template>
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="rounded-lg bg-gray-900 p-8 text-gray-200 shadow-lg">
            <!-- Header with Navigation -->
            <div class="mb-8 flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <h2 class="text-3xl font-bold">
                        {{ clientId ? 'Client Reservations' : 'Clients Reservations' }}
                        <span v-if="clientName" class="ml-2 text-lg text-gray-300"> ({{ clientName }}) </span>
                    </h2>
                    <p class="mt-2 text-gray-400">
                        {{ clientId ? `Showing reservations for client: ${clientName}` : 'Showing reservations for all clients approved by you' }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="/receptionist/clients" class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white transition hover:bg-blue-700">
                        Manage Clients
                    </a>
                    <a
                        href="/receptionist/clients/my-approved"
                        class="rounded-lg bg-green-600 px-4 py-2 font-semibold text-white transition hover:bg-green-700"
                    >
                        My Approved Clients
                    </a>
                    <a
                        href="/receptionist/reservations"
                        class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white transition hover:bg-indigo-700"
                    >
                        Pending Reservations
                    </a>
                </div>
            </div>

            <div v-if="clientsReservations.data.length === 0" class="py-8 text-center">
                <p class="text-lg text-gray-300">No reservations found for your approved clients.</p>
            </div>

            <!-- Data Table -->
            <div v-else class="overflow-hidden rounded-lg border border-gray-700 bg-gray-800">
                <DataTable
                    :columns="columns"
                    :data="clientsReservations.data"
                    :pagination="{
                        pageSize: 10,
                        pageIndex: currentPage - 1,
                        totalItems: clientsReservations.total,
                        manualPagination: true,
                    }"
                    @page-change="handlePageChange"
                    class="text-gray-200"
                >
                    <!-- Client Name Cell Template -->
                    <template #cell-client.name="{ row }">
                        {{ row.client ? row.client.name : 'N/A' }}
                    </template>

                    <!-- Room Number Cell Template -->
                    <template #cell-room.room_number="{ row }">
                        {{ row.room ? row.room.room_number : 'N/A' }}
                    </template>

                    <!-- Price Cell Template -->
                    <template #cell-price_paid="{ row }"> ${{ row.price_paid }} </template>

                    <!-- Actions Cell Template -->
                    <template #cell-actions="{ row }">
                        <div class="flex space-x-2">
                            <a
                                :href="`/receptionist/reservations/${row.id}`"
                                class="rounded-md border border-gray-600 bg-gray-700 px-3 py-1 text-sm font-medium text-gray-200 hover:bg-gray-600"
                            >
                                View
                            </a>
                            <a
                                :href="`/receptionist/reservations/${row.id}/edit`"
                                class="rounded-md border border-gray-600 bg-gray-700 px-3 py-1 text-sm font-medium text-gray-200 hover:bg-gray-600"
                            >
                                Edit
                            </a>
                            <button
                                v-if="row.status === 'pending'"
                                @click="approveReservation(row)"
                                class="rounded-md bg-green-700 px-3 py-1 text-sm font-medium text-white hover:bg-green-600"
                            >
                                Approve
                            </button>
                        </div>
                    </template>
                </DataTable>

                <!-- Enhanced Pagination -->
                <div v-if="clientsReservations.data.length > 0" class="mt-4 flex items-center justify-between">
                    <div class="text-sm text-gray-400">
                        Showing {{ clientsReservations.from }} to {{ clientsReservations.to }} of {{ clientsReservations.total }} reservations
                    </div>

                    <!-- Page Size Selector -->
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center">
                            <span class="mr-2 text-sm text-gray-400">Per page:</span>
                            <select
                                v-model="perPage"
                                @change="sortAndPaginate(1, perPage, currentSort.field, currentSort.direction)"
                                class="rounded-md bg-gray-700 px-2 py-1 text-sm text-gray-200"
                            >
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </div>

                        <!-- Page Navigation -->
                        <div class="flex space-x-2">
                            <button
                                v-for="page in clientsReservations.links"
                                :key="page.label"
                                @click="
                                    page.url &&
                                    sortAndPaginate(
                                        page.label === '&laquo; Previous'
                                            ? clientsReservations.current_page - 1
                                            : page.label === 'Next &raquo;'
                                              ? clientsReservations.current_page + 1
                                              : parseInt(page.label),
                                        perPage,
                                        currentSort.field,
                                        currentSort.direction,
                                    )
                                "
                                :disabled="!page.url"
                                :class="[
                                    'rounded-md px-3 py-1 text-sm',
                                    page.active
                                        ? 'bg-blue-600 text-white'
                                        : page.url
                                          ? 'bg-gray-700 text-gray-200 hover:bg-gray-600'
                                          : 'cursor-not-allowed bg-gray-800 text-gray-500',
                                ]"
                                v-html="page.label"
                            ></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import DataTable from '@/components/ui/DataTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';
defineOptions({ layout: AppLayout });
// Props
const props = defineProps({
    clientsReservations: {
        type: Object,
        required: true,
    },
    clientId: {
        type: [Number, String],
        default: null,
    },
    clientName: {
        type: String,
        default: null,
    },
});

// State
const showApproveDialog = ref(false);
const selectedReservation = ref(null);
const currentSort = ref({
    field: 'created_at',
    direction: 'desc',
});
const perPage = ref(10);

// Table columns definition
const columns = [
    {
        accessorKey: 'client.name',
        header: 'Client Name',
    },
    {
        accessorKey: 'accompany_number',
        header: 'Accompany Number',
    },
    {
        accessorKey: 'room.room_number',
        header: 'Room Number',
    },
    {
        accessorKey: 'price_paid',
        header: 'Client Paid Price',
    },
    {
        id: 'actions',
        header: 'Actions',
        enableSorting: false,
    },
];

// Computed
const currentPage = computed(() => {
    return props.clientsReservations.current_page || 1;
});

// Methods
// Enhanced pagination with sorting
const sortAndPaginate = (page = 1, perPage = 10, sortBy = 'created_at', sortDir = 'desc') => {
    // Update current sort state
    currentSort.value = {
        field: sortBy,
        direction: sortDir,
    };

    // Navigate with new parameters
    router.visit(window.location.pathname, {
        data: {
            page,
            per_page: perPage,
            sort_by: sortBy,
            sort_dir: sortDir,
            id: props.clientId, // Keep the client ID if we're viewing a specific client's reservations
        },
        preserveScroll: true,
        preserveState: false,
        replace: true,
    });
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString();
    } catch (e) {
        return dateString;
    }
};

const approveReservation = async (reservation) => {
    // Store the selected reservation
    selectedReservation.value = reservation;

    // Show confirmation before submitting
    if (confirm('Are you sure you want to approve this reservation?')) {
        try {
            // First, approve the reservation
            console.log('Approving reservation:', reservation);

            // Prepare the data to send
            const data = {
                status: 'confirmed',
                room_id: reservation.room_id,
                client_id: reservation.client_id,
                accompany_number: reservation.accompany_number,
                price_paid: reservation.price_paid,
                _method: 'PUT', // For method spoofing
            };

            // Use axios to make the request
            const response = await axios.post(`/receptionist/reservations/${reservation.id}`, data);
            console.log('Reservation approval response:', response.data);

            // Show success message for the reservation
            alert('Reservation approved successfully!');

            // Check if client is already approved from the response
            const clientApproved = response.data.client_approved;

            // If the client is not approved, redirect to the clients page
            if (reservation.client_id && !clientApproved) {
                router.visit('/receptionist/clients', {
                    onSuccess: () => {
                        console.log('Redirected to clients page to approve the client');
                    },
                });
            } else {
                // Otherwise, just reload the current page
                router.visit(window.location.pathname, {
                    method: 'get',
                    preserveScroll: false,
                    preserveState: false,
                    replace: true,
                    onSuccess: () => {
                        console.log('Page reloaded after reservation approval');
                    },
                });
            }
        } catch (error) {
            console.error('Error approving reservation:', error);
            alert('Could not approve reservation due to a technical issue. Please try refreshing the page.');
        }
    }
};

const getStatusClass = (status) => {
    const classes = {
        confirmed: 'bg-green-900 text-green-200',
        checked_in: 'bg-blue-900 text-blue-200',
        'checked-in': 'bg-blue-900 text-blue-200',
        checked_out: 'bg-gray-700 text-gray-200',
        'checked-out': 'bg-gray-700 text-gray-200',
        pending: 'bg-yellow-900 text-yellow-200',
        cancelled: 'bg-red-900 text-red-200',
    };
    return classes[status] || 'bg-gray-700 text-gray-200';
};

const handlePageChange = (pageIndex) => {
    const page = pageIndex + 1;
    const baseUrl = props.clientId ? `/receptionist/clients/${props.clientId}/reservations` : `/receptionist/clients/reservations`;

    // Build query parameters
    const params = new URLSearchParams();
    params.append('page', page);

    router.visit(`${baseUrl}?${params.toString()}`, {
        preserveScroll: true,
        preserveState: false,
        replace: true,
    });
};
</script>

<style scoped>
.pagination-link {
    @apply rounded-md px-3 py-1 text-sm;
}

.pagination-link-active {
    @apply bg-blue-600 text-white;
}

.pagination-link-inactive {
    @apply bg-gray-700 text-gray-200 hover:bg-gray-600;
}

.pagination-link-disabled {
    @apply cursor-not-allowed bg-gray-800 text-gray-500;
}
</style>
