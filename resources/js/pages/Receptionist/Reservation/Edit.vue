<template>
    <div class="mx-auto max-w-4xl px-4 py-8">
        <div class="rounded-lg bg-gray-900 p-8 text-gray-200 shadow-lg">
            <!-- Header -->
            <h2 class="mb-6 text-3xl font-bold">Edit Reservation</h2>

            <!-- Form -->
            <form @submit.prevent="updateReservation" class="space-y-6">
                <!-- Room Selection -->
                <div>
                    <Label for="room_id" class="mb-2 block text-sm font-semibold">Room</Label>
                    <Select v-model="reservation.room_id" required>
                        <SelectTrigger class="w-full rounded-lg border border-gray-600 bg-gray-800 p-3 focus:ring-2 focus:ring-blue-500">
                            <SelectValue placeholder="Select a room" />
                        </SelectTrigger>
                        <SelectContent class="border-gray-600 bg-gray-800 text-gray-200">
                            <SelectItem v-for="room in availableRooms" :key="room.id" :value="room.id"> Room {{ room.room_number }} </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Accompany Number -->
                <div>
                    <Label for="accompany_number" class="mb-2 block text-sm font-semibold">Accompany Number</Label>
                    <Input
                        v-model="reservation.accompany_number"
                        type="number"
                        id="accompany_number"
                        min="1"
                        required
                        class="w-full rounded-lg border border-gray-600 bg-gray-800 p-3 focus:ring-2 focus:ring-blue-500"
                    />
                </div>

                <!-- Check-in Date -->
                <div>
                    <Label for="check_in_date" class="mb-2 block text-sm font-semibold">Check-in Date</Label>
                    <Input
                        v-model="reservation.check_in_date"
                        type="date"
                        id="check_in_date"
                        required
                        class="w-full rounded-lg border border-gray-600 bg-gray-800 p-3 focus:ring-2 focus:ring-blue-500"
                    />
                </div>

                <!-- Check-out Date -->
                <div>
                    <Label for="check_out_date" class="mb-2 block text-sm font-semibold">Check-out Date</Label>
                    <Input
                        v-model="reservation.check_out_date"
                        type="date"
                        id="check_out_date"
                        required
                        class="w-full rounded-lg border border-gray-600 bg-gray-800 p-3 focus:ring-2 focus:ring-blue-500"
                    />
                </div>

                <!-- Price Paid -->
                <div>
                    <Label for="price_paid" class="mb-2 block text-sm font-semibold">Price Paid</Label>
                    <Input
                        v-model="reservation.price_paid"
                        type="number"
                        id="price_paid"
                        min="0"
                        required
                        class="w-full rounded-lg border border-gray-600 bg-gray-800 p-3 focus:ring-2 focus:ring-blue-500"
                    />
                </div>

                <!-- Status -->
                <div>
                    <Label for="status" class="mb-2 block text-sm font-semibold">Status</Label>
                    <Select v-model="reservation.status" required>
                        <SelectTrigger class="w-full rounded-lg border border-gray-600 bg-gray-800 p-3 focus:ring-2 focus:ring-blue-500">
                            <SelectValue placeholder="Select status" />
                        </SelectTrigger>
                        <SelectContent class="border-gray-600 bg-gray-800 text-gray-200">
                            <SelectItem value="pending">Pending</SelectItem>
                            <SelectItem value="confirmed">Confirmed</SelectItem>
                            <SelectItem value="checked_in">Checked In</SelectItem>
                            <SelectItem value="checked_out">Checked Out</SelectItem>
                            <SelectItem value="cancelled">Cancelled</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3">
                    <Button
                        @click="goBack"
                        variant="outline"
                        type="button"
                        class="rounded-lg border border-gray-600 bg-gray-800 px-6 py-3 font-semibold text-gray-200 hover:bg-gray-700"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" class="rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white transition hover:bg-blue-700">
                        Save Changes
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select/index';
import AppLayout from '@/layouts/AppLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { defineProps, ref } from 'vue';
defineOptions({ layout: AppLayout });
const props = defineProps({
    reservation: {
        type: Object,
        required: true,
    },
    availableRooms: {
        type: Array,
        required: true,
    },
});

// Create a form object with the reservation data
const form = useForm({
    room_id: props.reservation.room_id,
    accompany_number: props.reservation.accompany_number,
    check_in_date: props.reservation.check_in_date,
    check_out_date: props.reservation.check_out_date,
    status: props.reservation.status,
    price_paid: props.reservation.price_paid,
});

// Create a reactive reference to the reservation for the UI
const reservation = ref({ ...props.reservation });

// Watch for changes in the reservation ref and update the form
const updateFormFromReservation = () => {
    form.room_id = reservation.value.room_id;
    form.accompany_number = reservation.value.accompany_number;
    form.check_in_date = reservation.value.check_in_date;
    form.check_out_date = reservation.value.check_out_date;
    form.status = reservation.value.status;
    form.price_paid = reservation.value.price_paid;
};

const updateReservation = async () => {
    // Update form data from the reservation ref
    updateFormFromReservation();

    // Log the data being sent
    console.log('Updating reservation:', form.data());

    try {
        // Use axios directly instead of Inertia to handle the JSON response
        const response = await axios.put(`/receptionist/reservations/${reservation.value.id}`, form.data(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
        });

        // Check if the request was successful
        if (response.data.success) {
            alert('Reservation updated successfully!');

            // Navigate back to the index page
            window.location.href = '/receptionist/reservations';
        } else {
            alert('Could not update reservation. Please try again.');
        }
    } catch (error) {
        console.error('Error updating reservation:', error);

        // Show more detailed error message if available
        if (error.response && error.response.data && error.response.data.message) {
            alert(`Error: ${error.response.data.message}`);
        } else {
            alert('Could not update reservation due to a technical issue. Please try refreshing the page.');
        }
    }
};

const goBack = () => {
    router.visit(route('receptionist.reservations.index'), {
        preserveScroll: true,
    });
};
</script>

<style scoped>
label {
    color: #e5e7eb;
}
</style>
