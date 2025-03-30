<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { Button } from "@/components/ui/button";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Card, CardContent } from "@/components/ui/card";

// Custom Components
import Header from './header.vue';
import LoggedHeader from './Loggedheader.vue';
import Footer from './footer.vue';
import RoomCard from './room.vue';
import CTAs from './ctas.vue';
import Features from './features.vue';

function useCurrentUser() {
    const page = usePage();
    return page.props.auth.user;
}

const user = useCurrentUser();
console.log(user);
const props = defineProps({ 
  rooms: Array, 
  pagination: Object 
});

const showBookingModal = ref(false);
const selectedRoom = ref(null);
const today = computed(() => new Date().toISOString().split('T')[0]);

const form = useForm({
  check_in_date: '',
  check_out_date: '',
  guests: 1,
  special_requests: '',
  room_id: null,
  total_price: 0,
});

const bookRoom = (room) => {
  if (room.status !== 'available') return;
  selectedRoom.value = room;
  form.check_in_date = today.value;
  form.check_out_date = '';
  form.guests = 1;
  form.special_requests = '';
  form.room_id = room.id;
  showBookingModal.value = true;
};

const closeBookingModal = () => {
  showBookingModal.value = false;
  selectedRoom.value = null;
};

const calculateNights = () => {
  const checkIn = new Date(form.check_in_date);
  const checkOut = new Date(form.check_out_date);
  return isNaN(checkIn) || isNaN(checkOut) ? 0 : Math.max(0, Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24)));
};

const calculateTotal = () => {
  const total = calculateNights() * (selectedRoom.value?.price / 100 || 0);
  form.total_price = total;
  return total.toFixed(2);
};

const submitBooking = () => {
  calculateTotal();
  form.post(route('hotel.reservations.store'));
};
</script>

<template>
  <div class="min-h-screen bg-white dark:bg-black">
    <LoggedHeader v-if="user" />
    <Header v-else />

    <CTAs />
    <main>
      <div class="relative bg-white dark:bg-black">
        <div class="container mx-auto px-4">
          <h2 class="text-3xl font-bold text-center mb-12 text-gray-900 dark:text-white">Our Rooms</h2>
          <div v-if="rooms.length === 0" class="text-center py-8">
            <p class="text-gray-500 dark:text-gray-300">No rooms available at the moment.</p>
          </div>
          <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <RoomCard v-for="room in rooms" :key="room.id" :room="room" @book-room="bookRoom" />
          </div>
        </div>
      </div>

      <Features />

      <!-- Booking Modal -->
      <Dialog :open="showBookingModal" @update:open="closeBookingModal">
        <DialogContent class="sm:max-w-2xl bg-white dark:bg-black">
          <DialogHeader>
            <DialogTitle class="text-gray-900 dark:text-white">Book Room {{ selectedRoom?.room_number }}</DialogTitle>
          </DialogHeader>

          <form @submit.prevent="submitBooking" class="space-y-4">
            <div class="space-y-2">
              <Label for="check_in_date">Check-in Date</Label>
              <Input
                id="check_in_date"
                v-model="form.check_in_date"
                type="date"
                required
                :min="today"
              />
            </div>

            <div class="space-y-2">
              <Label for="check_out_date">Check-out Date</Label>
              <Input
                id="check_out_date"
                v-model="form.check_out_date"
                type="date"
                required
                :min="form.check_in_date || today"
              />
            </div>

            <div class="space-y-2">
              <Label for="guests">Number of Guests</Label>
              <Input
                id="guests"
                v-model="form.guests"
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
                v-model="form.special_requests"
                rows="3"
              />
            </div>

            <Card class="bg-white dark:bg-black">
              <CardContent class="pt-6 space-y-2">
                <div class="flex justify-between text-gray-900 dark:text-white">
                  <span class="text-gray-500 dark:text-gray-400">Room Rate:</span>
                  <span>${{ selectedRoom?.price/100 || 0 }} per night</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-muted-foreground">Nights:</span>
                  <span>{{ calculateNights() }}</span>
                </div>
                <div class="flex justify-between font-bold">
                  <span>Total:</span>
                  <span>${{ calculateTotal() }}</span>
                </div>
              </CardContent>
            </Card>

            <DialogFooter>
              <Button
                type="button"
                variant="outline"
                @click="closeBookingModal"
              >
                Cancel
              </Button>
              <Button
                type="submit"
                :disabled="form.processing"
              >
                {{ form.processing ? 'Processing...' : 'Proceed to Payment' }}
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
    </main>

    <Footer />
  </div>
</template>
