<template>
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="rounded-lg bg-gray-900 p-8 text-gray-200 shadow-lg">
            <!-- Header with Navigation -->
            <div class="mb-8 flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <h2 class="text-3xl font-bold">All Clients</h2>
                    <p class="mt-2 text-gray-400">System-wide view of all clients</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="/receptionist/clients" class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white transition hover:bg-blue-700">
                        Manage Clients
                    </a>
                    <a
                        href="/receptionist/clients/my-approved"
                        class="rounded-lg bg-purple-600 px-4 py-2 font-semibold text-white transition hover:bg-purple-700"
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

            <!-- All Clients Section -->
            <div>
                <div v-if="allClients.data.length === 0" class="py-8 text-center">
                    <p class="text-lg text-gray-300">No clients found in the system.</p>
                </div>

                <div v-else class="overflow-hidden rounded-lg border border-gray-700 bg-gray-800">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead class="bg-gray-800">
                            <tr>
                                <th
                                    @click="
                                        sortAndPaginate(
                                            1,
                                            10,
                                            'name',
                                            currentSort.field === 'name' && currentSort.direction === 'asc' ? 'desc' : 'asc',
                                        )
                                    "
                                    scope="col"
                                    class="cursor-pointer px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300 hover:bg-gray-700"
                                >
                                    Client Name
                                    <span v-if="currentSort.field === 'name'" class="ml-1">
                                        {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </th>
                                <th
                                    @click="
                                        sortAndPaginate(
                                            1,
                                            10,
                                            'email',
                                            currentSort.field === 'email' && currentSort.direction === 'asc' ? 'desc' : 'asc',
                                        )
                                    "
                                    scope="col"
                                    class="cursor-pointer px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300 hover:bg-gray-700"
                                >
                                    Email
                                    <span v-if="currentSort.field === 'email'" class="ml-1">
                                        {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </th>
                                <th
                                    @click="
                                        sortAndPaginate(
                                            1,
                                            10,
                                            'mobile',
                                            currentSort.field === 'mobile' && currentSort.direction === 'asc' ? 'desc' : 'asc',
                                        )
                                    "
                                    scope="col"
                                    class="cursor-pointer px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300 hover:bg-gray-700"
                                >
                                    Mobile
                                    <span v-if="currentSort.field === 'mobile'" class="ml-1">
                                        {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </th>
                                <th
                                    @click="
                                        sortAndPaginate(
                                            1,
                                            10,
                                            'country',
                                            currentSort.field === 'country' && currentSort.direction === 'asc' ? 'desc' : 'asc',
                                        )
                                    "
                                    scope="col"
                                    class="cursor-pointer px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300 hover:bg-gray-700"
                                >
                                    Country
                                    <span v-if="currentSort.field === 'country'" class="ml-1">
                                        {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </th>
                                <th
                                    @click="
                                        sortAndPaginate(
                                            1,
                                            10,
                                            'is_approved',
                                            currentSort.field === 'is_approved' && currentSort.direction === 'asc' ? 'desc' : 'asc',
                                        )
                                    "
                                    scope="col"
                                    class="cursor-pointer px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300 hover:bg-gray-700"
                                >
                                    Status
                                    <span v-if="currentSort.field === 'is_approved'" class="ml-1">
                                        {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700 bg-gray-800">
                            <tr v-for="client in allClients.data" :key="client.id" class="hover:bg-gray-700">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm font-medium text-gray-200">{{ client.name }}</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm text-gray-300">{{ client.email }}</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm text-gray-300">{{ client.mobile || 'N/A' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm text-gray-300">{{ client.country || 'N/A' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span :class="getStatusClass(client)" class="rounded-full px-2 py-1 text-xs font-medium">
                                        {{ getStatusText(client) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <!-- Edit/Delete Actions (Admin Only) -->
                                        <button
                                            v-if="isAdmin"
                                            @click="editClient(client.id)"
                                            class="rounded-md bg-blue-700 px-3 py-1 text-sm font-medium text-white hover:bg-blue-600"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            v-if="isAdmin"
                                            @click="deleteClient(client.id)"
                                            class="rounded-md bg-red-700 px-3 py-1 text-sm font-medium text-white hover:bg-red-600"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Enhanced Pagination -->
            <div v-if="allClients.data.length > 0" class="mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-400">Showing {{ allClients.from }} to {{ allClients.to }} of {{ allClients.total }} clients</div>

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
                            v-for="page in allClients.links"
                            :key="page.label"
                            @click="
                                page.url &&
                                sortAndPaginate(
                                    page.label === '&laquo; Previous'
                                        ? allClients.current_page - 1
                                        : page.label === 'Next &raquo;'
                                          ? allClients.current_page + 1
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

            <!-- Client Statistics Dashboard -->
            <div class="mt-8 rounded-lg border border-gray-700 bg-gray-800 p-6">
                <h3 class="mb-4 text-xl font-semibold text-gray-100">Client Statistics</h3>
                <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="rounded-lg bg-gray-700 p-4">
                        <div class="text-sm text-gray-400">Total Clients</div>
                        <div class="text-2xl font-bold text-gray-100">{{ clientStats.totalClients }}</div>
                    </div>
                    <div class="rounded-lg bg-green-900 p-4">
                        <div class="text-sm text-gray-300">Approved Clients</div>
                        <div class="text-2xl font-bold text-gray-100">{{ clientStats.approvedClients }}</div>
                    </div>
                    <div class="rounded-lg bg-yellow-900 p-4">
                        <div class="text-sm text-gray-300">Pending Clients</div>
                        <div class="text-2xl font-bold text-gray-100">{{ clientStats.pendingClients }}</div>
                    </div>
                </div>

                <h4 class="mb-3 text-lg font-semibold text-gray-100">Recent Reservations</h4>
                <div v-if="recentReservations.length === 0" class="rounded-lg bg-gray-700 py-4 text-center">
                    <p class="text-gray-400">No recent reservations found.</p>
                </div>
                <div v-else class="overflow-hidden rounded-lg border border-gray-700 bg-gray-700">
                    <table class="min-w-full divide-y divide-gray-600">
                        <thead class="bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Client</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Room</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Check-in</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Check-out</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-600 bg-gray-700">
                            <tr v-for="reservation in recentReservations" :key="reservation.id" class="hover:bg-gray-600">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm font-medium text-gray-200">
                                        {{ reservation.client ? reservation.client.name : 'N/A' }}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm text-gray-300">
                                        {{ reservation.room ? reservation.room.room_number : 'N/A' }}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span :class="getReservationStatusClass(reservation.status)" class="rounded-full px-2 py-1 text-xs font-medium">
                                        {{ reservation.status }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm text-gray-300">{{ formatDate(reservation.check_in_date) }}</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm text-gray-300">{{ formatDate(reservation.check_out_date) }}</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Confirmation Dialog -->
            <div v-if="showConfirmDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                <div class="w-full max-w-md rounded-lg bg-gray-800 p-6 text-gray-200 shadow-xl">
                    <h3 class="text-xl font-semibold text-gray-100">{{ confirmDialogTitle }}</h3>
                    <p class="mt-2 text-gray-400">{{ confirmDialogMessage }}</p>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            class="rounded-md border border-gray-600 bg-gray-700 px-4 py-2 text-sm font-medium text-gray-200 hover:bg-gray-600"
                            @click="cancelConfirmation"
                        >
                            Cancel
                        </button>
                        <button class="rounded-md bg-red-700 px-4 py-2 text-sm font-medium text-white hover:bg-red-600" @click="confirmDelete()">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';
defineOptions({ layout: AppLayout });
// Props
const props = defineProps({
    allClients: {
        type: Object,
        required: true,
    },
    clientStats: {
        type: Object,
        default: () => ({
            totalClients: 0,
            approvedClients: 0,
            pendingClients: 0,
        }),
    },
    recentReservations: {
        type: Array,
        default: () => [],
    },
    userRole: {
        type: String,
        default: 'receptionist',
    },
    isAdmin: {
        type: Boolean,
        default: false,
    },
    currentUserId: {
        type: Number,
        required: true,
    },
    debug: {
        type: Object,
        default: null,
    },
});

// State
const showConfirmDialog = ref(false);
const confirmDialogTitle = ref('');
const confirmDialogMessage = ref('');
const selectedClientId = ref(null);
const currentSort = ref({
    field: 'created_at',
    direction: 'desc',
});
const perPage = ref(10);

// No need for co need for computed property, using the prop directly

// Methods
const goToPage = (url) => {
    if (!url) return;
    router.visit(url, {
        preserveScroll: true,
        preserveState: false,
    });
};

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

const getReservationStatusClass = (status) => {
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

const getStatusClass = (client) => {
    if (client.is_approved) {
        return 'bg-green-900 text-green-200';
    } else {
        return 'bg-yellow-900 text-yellow-200';
    }
};

const getStatusText = (client) => {
    if (client.is_approved) {
        return 'Approved';
    } else {
        return 'Pending';
    }
};

const cancelConfirmation = () => {
    showConfirmDialog.value = false;
    selectedClientId.value = null;
};

// Edit client method
const editClient = (clientId) => {
    // Navigate to the edit page using Inertia
    router.visit(`/receptionist/clients/${clientId}/edit`, {
        method: 'get',
        preserveState: false,
    });
};

// Delete client method
const deleteClient = (clientId) => {
    selectedClientId.value = clientId;
    confirmDialogTitle.value = 'Confirm Client Deletion';
    confirmDialogMessage.value =
        'Are you sure you want to delete this client? This action cannot be undone and will remove all associated reservations.';
    showConfirmDialog.value = true;
};

// Confirm delete method
const confirmDelete = async () => {
    if (!selectedClientId.value) return;

    try {
        // Show loading state
        showConfirmDialog.value = false;

        // Use axios to make the request
        await axios.delete(`/receptionist/clients/${selectedClientId.value}`);

        // Use Inertia router to reload the page with fresh data
        router.visit(window.location.pathname, {
            method: 'get',
            preserveScroll: false,
            preserveState: false,
            replace: true,
            onSuccess: () => {
                console.log('Client deleted successfully');
            },
        });
    } catch (error) {
        console.error('Error deleting client:', error);
        alert('Could not delete client due to a technical issue. Please try refreshing the page.');
    }
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
