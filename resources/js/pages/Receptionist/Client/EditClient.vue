<template>
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="rounded-lg bg-gray-900 p-8 text-gray-200 shadow-lg">
            <!-- Header with Navigation -->
            <div class="mb-8 flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <h2 class="text-3xl font-bold">Edit Client</h2>
                    <p class="mt-2 text-gray-400">Update client information</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a
                        href="/receptionist/clients/all"
                        class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white transition hover:bg-blue-700"
                    >
                        Back to All Clients
                    </a>
                    <a
                        href="/receptionist/clients"
                        class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white transition hover:bg-indigo-700"
                    >
                        Pending Clients
                    </a>
                </div>
            </div>

            <!-- Edit Client Form -->
            <div class="rounded-lg border border-gray-700 bg-gray-800 p-6">
                <form @submit.prevent="updateClient">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-300">Name</label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-600 bg-gray-700 text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                            <div v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</div>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-300">Email</label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-1 block w-full rounded-md border-gray-600 bg-gray-700 text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                            <div v-if="form.errors.email" class="mt-1 text-sm text-red-500">{{ form.errors.email }}</div>
                        </div>

                        <!-- Mobile -->
                        <div>
                            <label for="mobile" class="block text-sm font-medium text-gray-300">Mobile</label>
                            <input
                                id="mobile"
                                v-model="form.mobile"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-600 bg-gray-700 text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            <div v-if="form.errors.mobile" class="mt-1 text-sm text-red-500">{{ form.errors.mobile }}</div>
                        </div>

                        <!-- Country -->
                        <div>
                            <label for="country" class="block text-sm font-medium text-gray-300">Country</label>
                            <input
                                id="country"
                                v-model="form.country"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-600 bg-gray-700 text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            <div v-if="form.errors.country" class="mt-1 text-sm text-red-500">{{ form.errors.country }}</div>
                        </div>

                        <!-- Gender -->
                        <div>
                            <label for="gender" class="block text-sm font-medium text-gray-300">Gender</label>
                            <select
                                id="gender"
                                v-model="form.gender"
                                class="mt-1 block w-full rounded-md border-gray-600 bg-gray-700 text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <div v-if="form.errors.gender" class="mt-1 text-sm text-red-500">{{ form.errors.gender }}</div>
                        </div>

                        <!-- Status Options -->
                        <div>
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center">
                                    <input
                                        id="is_approved"
                                        v-model="form.is_approved"
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-gray-600 bg-gray-700 text-indigo-600 focus:ring-indigo-500"
                                    />
                                    <label for="is_approved" class="ml-2 block text-sm text-gray-300">Approved</label>
                                </div>
                                <!-- <div class="flex items-center">
                  <input
                    id="is_banned"
                    v-model="form.is_banned"
                    type="checkbox"
                    class="h-4 w-4 rounded border-gray-600 bg-gray-700 text-indigo-600 focus:ring-indigo-500"
                  />
                  <label for="is_banned" class="ml-2 block text-sm text-gray-300">Banned</label>
                </div> -->
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-6 flex justify-end">
                        <button
                            type="submit"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Updating...' : 'Update Client' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Client Reservations Section -->
            <div v-if="client.reservations && client.reservations.length > 0" class="mt-8">
                <h3 class="mb-4 text-xl font-semibold text-gray-100">Client Reservations</h3>
                <div class="overflow-hidden rounded-lg border border-gray-700 bg-gray-800">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead class="bg-gray-800">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Room</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Check-in</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Check-out</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700 bg-gray-800">
                            <tr v-for="reservation in client.reservations" :key="reservation.id" class="hover:bg-gray-700">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm font-medium text-gray-200">{{ reservation.room ? reservation.room.room_number : 'N/A' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm text-gray-300">{{ formatDate(reservation.check_in_date) }}</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm text-gray-300">{{ formatDate(reservation.check_out_date) }}</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span :class="getReservationStatusClass(reservation.status)" class="rounded-full px-2 py-1 text-xs font-medium">
                                        {{ reservation.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
defineOptions({ layout: AppLayout });
// Props
const props = defineProps({
    client: {
        type: Object,
        required: true,
    },
    isAdmin: {
        type: Boolean,
        default: false,
    },
    currentUserId: {
        type: Number,
        required: true,
    },
});

// Form setup
const form = useForm({
    name: props.client.name,
    email: props.client.email,
    mobile: props.client.mobile || '',
    country: props.client.country || '',
    gender: props.client.gender || '',
    is_approved: Boolean(props.client.is_approved),
    is_banned: Boolean(props.client.is_banned),
});

// Methods
const updateClient = () => {
    form.put(`/receptionist/clients/${props.client.id}`);
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
</script>
