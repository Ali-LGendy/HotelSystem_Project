<template>
    <div class="mt-8 rounded-lg bg-gray-900 p-8 text-gray-200 shadow-lg">
        <h3 class="mb-4 text-2xl font-bold text-gray-100">Client Management Dashboard</h3>

        <!-- Client Statistics -->
        <div class="mb-6 rounded-lg bg-gray-800 p-4">
            <h4 class="mb-2 text-xl font-semibold text-gray-100">Client Statistics</h4>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-lg bg-green-900 p-4">
                    <div class="text-sm text-gray-300">Total Approved Clients</div>
                    <div class="text-2xl font-bold text-gray-100">{{ stats.totalApproved }}</div>
                </div>
                <div class="rounded-lg bg-blue-900 p-4">
                    <div class="text-sm text-gray-300">Active Reservations</div>
                    <div class="text-2xl font-bold text-gray-100">{{ stats.activeReservations }}</div>
                </div>
                <div class="rounded-lg bg-yellow-900 p-4">
                    <div class="text-sm text-gray-300">Pending Reservations</div>
                    <div class="text-2xl font-bold text-gray-100">{{ stats.pendingReservations }}</div>
                </div>
            </div>
        </div>

        <!-- Recent Reservations -->
        <div>
            <h4 class="mb-4 text-xl font-semibold text-gray-100">Recent Reservations</h4>
            <div v-if="reservations.length === 0" class="rounded-lg bg-gray-800 py-4 text-center">
                <p class="text-gray-400">No recent reservations found.</p>
            </div>
            <div v-else class="overflow-hidden rounded-lg border border-gray-700 bg-gray-800">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-800">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Client Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Room Number</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Check-in Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700 bg-gray-800">
                        <tr v-for="reservation in reservations" :key="reservation.id" class="hover:bg-gray-700">
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
                                <div class="text-sm text-gray-300">{{ formatDate(reservation.check_in_date) }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span :class="getStatusClass(reservation.status)" class="rounded-full px-2 py-1 text-xs font-medium">
                                    {{ reservation.status }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button
                                        v-if="reservation.status === 'pending'"
                                        @click="approveReservation(reservation)"
                                        class="rounded-md bg-green-700 px-3 py-1 text-sm font-medium text-white hover:bg-green-600"
                                    >
                                        Approve
                                    </button>
                                    <button
                                        @click="navigateTo(`/receptionist/reservations/${reservation.id}`)"
                                        class="rounded-md border border-gray-600 bg-gray-700 px-3 py-1 text-sm font-medium text-gray-200 hover:bg-gray-600"
                                    >
                                        View
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
defineOptions({ layout: AppLayout });
// Props
const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
    reservations: {
        type: Array,
        default: () => [],
    },
});

// For debugging
console.log('ClientStatsDashboard props:', {
    stats: props.stats,
    reservations: props.reservations,
});

// Methods
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString();
    } catch (e) {
        return dateString;
    }
};

// Navigation method using Inertia
const navigateTo = (url) => {
  router.get(url, {}, {
    preserveScroll: false,
    preserveState: false,
    replace: false
  });
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

const approveReservation = async (reservation) => {
    if (confirm('Are you sure you want to approve this reservation?')) {
        try {
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

            // Show success message
            alert('Reservation approved successfully!');

            // Reload the current page
            router.visit(window.location.pathname, {
                method: 'get',
                preserveScroll: false,
                preserveState: false,
                replace: true,
                onSuccess: () => {
                    console.log('Page reloaded after reservation approval');
                },
            });
        } catch (error) {
            console.error('Error approving reservation:', error);
            alert('Could not approve reservation due to a technical issue. Please try refreshing the page.');
        }
    }
};
</script>
