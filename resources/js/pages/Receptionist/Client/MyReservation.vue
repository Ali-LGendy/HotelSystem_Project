<template>
    <div class="min-h-screen bg-gray-50 px-4 py-12 dark:bg-gray-900">
        <div class="container mx-auto">
            <div class="overflow-hidden rounded-lg bg-white shadow-md dark:bg-gray-800">
                <div class="border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-600 dark:bg-gray-700">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">My Reservations</h2>
                </div>

                <div v-if="reservations.length === 0" class="p-6 text-center text-gray-500 dark:text-gray-400">No reservations found.</div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                    Accompany Number
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                    Room Number
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                    Paid Price
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                    Status
                                </th>
                                <th  class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                    actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            <tr
                                v-for="reservation in reservations"
                                :key="reservation.id"
                                class="transition duration-150 ease-in-out hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-200">
                                    {{ reservation.accompany_number }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-200">
                                    {{ reservation.room_id }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900 dark:text-gray-200">
                                    ${{ reservation.price_paid.toFixed(3) /100 }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <span
                                        :class="getStatusBadgeClass(reservation.status)"
                                        class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                    >
                                        {{ reservation.status }}
                                    </span>
                                </td>
                                <td v-if="reservation.status == 'pending'" class="whitespace-nowrap px-6 py-4 text-right">
                                    <Button @click="goToStripe(reservation.id)">Proceed to Payment</Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="pagination"
                    class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800"
                >
                    <Pagination :pagination="pagination" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import Pagination from '@/Components/Pagination.vue';
import { Button } from '@/components/ui/button';

// Props from controller
const props = defineProps({
    reservations: {
        type: Array,
        default: () => [],
    },
    pagination: {
        type: Object,
        default: null,
    },
});

console.log('Reservations:', props.reservations);

const goToStripe = function(reservationId) {
        // Replace '69' with the actual reservation ID or a variable.
  
        // Construct the URL with the reservation ID.
        const stripeCheckoutUrl = `/reservations/checkout/${reservationId}`;
  
        // Redirect the user to the Stripe checkout URL.
        window.location.href = stripeCheckoutUrl;
      }

// Method to get status badge class
const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'completed':
            return 'bg-green-100 text-green-800';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};
</script>
