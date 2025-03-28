<template>
    <div class="mt-8 rounded-lg bg-gray-900 p-8 text-gray-200 shadow-lg">
        <h3 class="mb-4 text-2xl font-bold text-gray-100">Approval Data</h3>

        <!-- Approval Status -->
        <div class="mb-6 rounded-lg bg-gray-800 p-4">
            <h4 class="mb-2 text-xl font-semibold text-gray-100">Approval Status</h4>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-lg bg-gray-700 p-4">
                    <div class="text-sm text-gray-400">Total Clients</div>
                    <div class="text-2xl font-bold text-gray-100">{{ stats.totalClients }}</div>
                </div>
                <div class="rounded-lg bg-green-900 p-4">
                    <div class="text-sm text-gray-300">Approved Clients</div>
                    <div class="text-2xl font-bold text-gray-100">{{ stats.approvedClients }}</div>
                </div>
                <div class="rounded-lg bg-yellow-900 p-4">
                    <div class="text-sm text-gray-300">Pending Clients</div>
                    <div class="text-2xl font-bold text-gray-100">{{ stats.pendingClients }}</div>
                </div>
            </div>
        </div>

        <!-- Recent Approvals -->
        <div class="mb-6">
            <h4 class="mb-4 text-xl font-semibold text-gray-100">Recent Approvals</h4>
            <div v-if="recentApprovals.length === 0" class="rounded-lg bg-gray-800 py-4 text-center">
                <p class="text-gray-400">No recent approvals found.</p>
            </div>
            <div v-else class="overflow-hidden rounded-lg border border-gray-700 bg-gray-800">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-800">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Client Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Email</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Approved On</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700 bg-gray-800">
                        <tr v-for="client in recentApprovals" :key="client.id" class="hover:bg-gray-700">
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm font-medium text-gray-200">{{ client.name }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm text-gray-300">{{ client.email }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm text-gray-300">{{ formatDate(client.approved_at) }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button
                                        @click="navigateTo(`/receptionist/clients/${client.id}/reservations`)"
                                        class="rounded-md bg-indigo-700 px-3 py-1 text-sm font-medium text-white hover:bg-indigo-600"
                                    >
                                        View Reservations
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pending Reservations for Approved Clients -->
        <div>
            <h4 class="mb-4 text-xl font-semibold text-gray-100">Pending Reservations for Approved Clients</h4>
            <div v-if="pendingReservations.length === 0" class="rounded-lg bg-gray-800 py-4 text-center">
                <p class="text-gray-400">No pending reservations found for your approved clients.</p>
            </div>
            <div v-else class="overflow-hidden rounded-lg border border-gray-700 bg-gray-800">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-800">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Client Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Room Number</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Check-in Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Check-out Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700 bg-gray-800">
                        <tr v-for="reservation in pendingReservations" :key="reservation.id" class="hover:bg-gray-700">
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
                                <div class="text-sm text-gray-300">{{ formatDate(reservation.check_out_date) }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button
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
        default: () => ({
            totalClients: 0,
            approvedClients: 0,
            pendingClients: 0,
        }),
    },
    recentApprovals: {
        type: Array,
        default: () => [],
    },
    pendingReservations: {
        type: Array,
        default: () => [],
    },
});

// For debugging
console.log('ApprovalDataTable props:', {
    stats: props.stats,
    recentApprovals: props.recentApprovals,
    pendingReservations: props.pendingReservations,
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
  router.visit(url, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
    onSuccess: () => {
      console.log('Navigation successful to:', url);
    },
    onError: (errors) => {
      console.error('Navigation error:', errors);
    }
  });
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
        check_in_date: reservation.check_in_date,
        check_out_date: reservation.check_out_date
      };

      // Use axios to make the request with proper method
      const response = await axios.put(`/receptionist/reservations/${reservation.id}`, data);
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
        }  catch (error) {
      console.error('Error approving reservation:', error.response?.data || error);
      alert('Could not approve reservation due to a technical issue. Please try refreshing the page.');
    }
      };
    
};
</script>
