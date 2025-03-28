<template>
  <div class="min-h-screen flex flex-col">
    <Header />
    <CTAs />
    <main class="flex-grow">
      <section id="rooms" class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
          <h2 class="text-3xl font-bold text-center mb-12">Our Rooms</h2>
          <div v-if="rooms.length === 0" class="text-center py-8">
            <p class="text-gray-500">No rooms available at the moment.</p>
          </div>
          <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <RoomCard v-for="room in rooms" :key="room.id" :room="room" @book-room="bookRoom" />
          </div>
        </div>
      </section>
      <Features />
      <div v-if="showBookingModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl p-8 max-h-[95vh] overflow-y-auto">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-bold">Book Room {{ selectedRoom?.room_number }}</h3>
            <button @click="closeBookingModal" class="text-gray-500 hover:text-gray-700">&times;</button>
          </div>
          <form @submit.prevent="submitBooking" class="space-y-4">
            <div>
              <label class="block text-gray-700 text-sm font-bold mb-2">Check-in Date</label>
              <input type="date" v-model="form.check_in_date" class="input-field" required :min="today">
            </div>
            <div>
              <label class="block text-gray-700 text-sm font-bold mb-2">Check-out Date</label>
              <input type="date" v-model="form.check_out_date" class="input-field" required :min="form.check_in_date || today">
            </div>
            <div>
              <label class="block text-gray-700 text-sm font-bold mb-2">Number of Guests</label>
              <input type="number" v-model="form.guests" min="1" :max="selectedRoom?.room_capacity || 1" class="input-field" required>
            </div>
            <div>
              <input type="hidden" v-model="form.room_id" value="selectedRoom.room_id" class="input-field" required>
            </div>
            <div>
              <label class="block text-gray-700 text-sm font-bold mb-2">Special Requests (Optional)</label>
              <textarea v-model="form.special_requests" class="input-field" rows="3"></textarea>
            </div>
            <div class="bg-gray-100 p-4 rounded-md">
              <div class="flex justify-between mb-2"><span>Room Rate:</span><span>${{ selectedRoom?.price || 0 }} per night</span></div>
              <div class="flex justify-between mb-2"><span>Nights:</span><span>{{ calculateNights() }}</span></div>
              <div class="flex justify-between font-bold"><span>Total:</span><span>${{ calculateTotal() }}</span></div>
            </div>
            <div class="flex justify-end space-x-3">
              <button type="button" @click="closeBookingModal" class="btn-secondary">Cancel</button>
              <button type="submit" class="btn-primary" :disabled="form.processing">
                {{ form.processing ? 'Processing...' : 'Proceed to Payment' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </main>
    <Footer />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import Header from './header.vue';
import Footer from './footer.vue';
import RoomCard from './room.vue';
import CTAs from './ctas.vue';
import Features from './features.vue';

const props = defineProps({ rooms: Array, pagination: Object });
const showBookingModal = ref(false);
const selectedRoom = ref(null);
const today = computed(() => new Date().toISOString().split('T')[0]);
const form = useForm({
  check_in_date: '',
  check_out_date: '',
  guests: 1,
  special_requests: '',
  room_id: null,
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
  return (calculateNights() * (selectedRoom.value?.price || 0)).toFixed(2);
};

const submitBooking = () => {
  form.post(route('hotel.reservations.store'), {
    onSuccess: ({ props }) => {
      // const reservationId = props?.reservation_id;
      // if (reservationId) {
      //   router.visit(route('hotel.reservations.edit', { reservation_id: reservationId }));
      // }
      console.log(form);
    },
  });
};
</script>

<style scoped>
.input-field {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 5px;
  outline: none;
  transition: border 0.3s;
}
.input-field:focus {
  border-color: #007bff;
}
.btn-primary {
  background-color: #007bff;
  color: white;
  padding: 10px 20px;
  border-radius: 5px;
  transition: background 0.3s;
}
.btn-primary:hover {
  background-color: #0056b3;
}
.btn-secondary {
  background-color: #ccc;
  padding: 10px 20px;
  border-radius: 5px;
}
</style>