<template>
  <div class="min-h-screen flex flex-col">
    <!-- Header Component -->
    <Header />
    <CTAs />

    <!-- Main Content -->
    <main class="flex-grow">
      <!-- Featured Rooms Section -->
      <section id="rooms" class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
          <h2 class="text-3xl font-bold text-center mb-12">Our Rooms</h2>

          <div v-if="rooms.length === 0" class="text-center py-8">
            <p class="text-gray-500">No rooms available at the moment.</p>
          </div>

          <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <RoomCard
              v-for="room in rooms"
              :key="room.id"
              :room="room"
              @book-room="bookRoom"
            />
          </div>

          <!-- Pagination -->
          <div class="mt-12">
            <Pagination v-if="pagination" :pagination="pagination" />
          </div>
        </div>
      </section>

      <Features />

      <!-- Booking Modal -->
      <div v-if="showBookingModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Book Room {{ selectedRoom?.room_number }}</h3>
            <button @click="closeBookingModal" class="text-gray-500 hover:text-gray-700">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <form @submit.prevent="submitBooking">
            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="check_in_date">
                Check-in Date
              </label>
              <input
                id="check_in_date"
                v-model="bookingForm.check_in_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
                :min="today"
              >
            </div>

            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="check_out_date">
                Check-out Date
              </label>
              <input
                id="check_out_date"
                v-model="bookingForm.check_out_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
                :min="bookingForm.check_in_date || today"
              >
            </div>

            <div class="mb-4">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="guests">
                Number of Guests
              </label>
              <input
                id="guests"
                v-model="bookingForm.guests"
                type="number"
                min="1"
                :max="selectedRoom?.room_capacity || 1"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              >
            </div>

            <div class="mb-6">
              <label class="block text-gray-700 text-sm font-bold mb-2" for="special_requests">
                Special Requests (Optional)
              </label>
              <textarea
                id="special_requests"
                v-model="bookingForm.special_requests"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                rows="3"
              ></textarea>
            </div>

            <div class="bg-gray-50 p-4 rounded-md mb-6">
              <div class="flex justify-between mb-2">
                <span>Room Rate:</span>
                <span>${{ selectedRoom?.price || 0 }} per night</span>
              </div>
              <div class="flex justify-between mb-2">
                <span>Nights:</span>
                <span>{{ calculateNights() }}</span>
              </div>
              <div class="flex justify-between font-bold">
                <span>Total:</span>
                <span>${{ calculateTotal() }}</span>
              </div>
            </div>

            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="closeBookingModal"
                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-100"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                :disabled="isSubmitting"
              >
                {{ isSubmitting ? 'Processing...' : 'Proceed to Payment' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </main>

    <!-- Footer Component -->
    <Footer />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import Header from './header.vue';
import Footer from './footer.vue';
import CTAs from './ctas.vue';
import RoomCard from './room.vue';
import Features from './features.vue';
import Pagination from '@/Components/Pagination.vue';

// Props from controller
const props = defineProps({
  rooms: {
    type: Array,
    default: () => []
  },
  pagination: {
    type: Object,
    default: null
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
const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'available':
      return 'bg-green-100 text-green-800';
    case 'occupied':
      return 'bg-blue-100 text-blue-800';
    case 'maintenance':
      return 'bg-yellow-100 text-yellow-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
};

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

  // Prepare the form data
  const formData = {
    room_id: selectedRoom.value.id,
    check_in_date: bookingForm.value.check_in_date,
    check_out_date: bookingForm.value.check_out_date,
    guests: bookingForm.value.guests,
    special_requests: bookingForm.value.special_requests,
    total_price: calculateTotal()
  };

  // Submit the booking
  router.post(route('hotel.reservations.store'), formData, {
    onSuccess: () => {
      closeBookingModal();
      // You might want to show a success message or redirect
    },
    onError: (errors) => {
      console.error('Booking errors:', errors);
      // Handle errors
    },
    onFinish: () => {
      isSubmitting.value = false;
    }
  });
};

// Lifecycle hooks
onMounted(() => {
  console.log('Available rooms:', props.rooms);
});
</script>