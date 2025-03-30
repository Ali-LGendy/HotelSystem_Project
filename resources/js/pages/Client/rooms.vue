<template>
  <div class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold text-center mb-8 text-gray-900 dark:text-white">Available Rooms</h2>

    <div v-if="rooms.length === 0" class="text-center py-8">
      <p class="text-gray-500 dark:text-gray-300">No rooms available at the moment.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <RoomCard
        v-for="room in rooms"
        :key="room.id"
        :room="room"
        @book-room="bookRoom"
      />
    </div>

    <Dialog :open="showBookingModal" @update:open="closeBookingModal">
      <DialogContent class="sm:max-w-md bg-white dark:bg-black">
        <DialogHeader>
          <DialogTitle class="text-gray-900 dark:text-white">Book Room {{ selectedRoom?.room_number }}</DialogTitle>
        </DialogHeader>

        <form @submit.prevent="submitBooking" class="space-y-4">
          <div class="space-y-2">
            <Label for="check_in_date">Check-in Date</Label>
            <Input
              id="check_in_date"
              v-model="bookingForm.check_in_date"
              type="date"
              required
              :min="today"
            />
          </div>

          <div class="space-y-2">
            <Label for="check_out_date">Check-out Date</Label>
            <Input
              id="check_out_date"
              v-model="bookingForm.check_out_date"
              type="date"
              required
              :min="bookingForm.check_in_date || today"
            />
          </div>

          <div class="space-y-2">
            <Label for="guests">Number of Guests</Label>
            <Input
              id="guests"
              v-model="bookingForm.guests"
              type="number"
              min="1"
              :max="selectedRoom?.room_capacity || 1"
              required
            />
          </div>

          <div class="space-y-2">
            <Label for="special_requests">Special Requests (Optional)</Label>
            <Textarea
              id="special_requests"
              v-model="bookingForm.special_requests"
              rows="3"
            />
          </div>

          <Card class="mt-6">
            <CardContent class="pt-6">
              <div class="space-y-2">
                <div class="flex justify-between">
                  <span class="text-muted-foreground">Room Rate:</span>
                  <span>${{ selectedRoom?.price || 0 }} per night</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-muted-foreground">Nights:</span>
                  <span>{{ calculateNights() }}</span>
                </div>
                <div class="flex justify-between font-bold">
                  <span>Total:</span>
                  <span>${{ calculateTotal() }}</span>
                </div>
              </div>
            </CardContent>
          </Card>

          <DialogFooter class="flex justify-end space-x-2">
            <Button
              type="button"
              variant="outline"
              @click="closeBookingModal"
            >
              Cancel
            </Button>
            <Button
              type="submit"
              :disabled="isSubmitting"
            >
              {{ isSubmitting ? 'Processing...' : 'Proceed to Payment' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import RoomCard from './room.vue';
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Label } from "@/components/ui/label";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogFooter,
} from "@/components/ui/dialog";
import { Card, CardContent } from "@/components/ui/card";

// Props
const props = defineProps({
  rooms: {
    type: Array,
    default: () => []
  }
});

// Reactive state
const showBookingModal = ref(false);
const selectedRoom = ref(null);
const isSubmitting = ref(false);
const bookingForm = ref({
  check_in_date: '',
  check_out_date: '',
  guests: 1,
  special_requests: ''
});

// Computed properties
const today = computed(() => {
  const date = new Date();
  return date.toISOString().split('T')[0];
});

// Methods
const bookRoom = (room) => {
  if (room.status !== 'available') return;

  selectedRoom.value = room;
  bookingForm.value = {
    check_in_date: today.value,
    check_out_date: '',
    guests: 1,
    special_requests: '',
    room_id: room.id
  };
  showBookingModal.value = true;
};

const closeBookingModal = () => {
  showBookingModal.value = false;
  selectedRoom.value = null;
};

const calculateNights = () => {
  if (!bookingForm.value.check_in_date || !bookingForm.value.check_out_date) return 0;

  const checkIn = new Date(bookingForm.value.check_in_date);
  const checkOut = new Date(bookingForm.value.check_out_date);
  const diffTime = checkOut - checkIn;
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

  return diffDays > 0 ? diffDays : 0;
};

const calculateTotal = () => {
  const nights = calculateNights();
  const price = selectedRoom.value?.price || 0;
  return (nights * price).toFixed(2);
};

const submitBooking = () => {
  if (!selectedRoom.value) return;

  isSubmitting.value = true;

  const formData = {
    room_id: selectedRoom.value.id,
    check_in_date: bookingForm.value.check_in_date,
    check_out_date: bookingForm.value.check_out_date,
    guests: bookingForm.value.guests,
    special_requests: bookingForm.value.special_requests,
    total_price: calculateTotal()
  };

  router.post(route('hotel.reservations.store'), formData, {
    onSuccess: () => {
      closeBookingModal();
    },
    onError: (errors) => {
      console.error('Booking errors:', errors);
    },
    onFinish: () => {
      isSubmitting.value = false;
    }
  });
};
</script>