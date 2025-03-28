<template>
  <div class="bg-white rounded-lg shadow-md overflow-hidden">
    <!-- Room Image (placeholder) -->
    <div class="h-48 bg-gray-300 relative">
      <div class="absolute top-0 right-0 bg-teal-500 text-white px-3 py-1 m-2 rounded-md">
        ${{ room.price }}/night
      </div>
    </div>
    <div class="p-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-bold">Room {{ room.room_number }}</h3>
        <span :class="getStatusBadgeClass(room.status)" class="px-2 py-1 rounded-full text-xs font-medium">
          {{ room.status }}
        </span>
      </div>
      <div class="mb-4">
        <p class="text-gray-600">
          <span class="font-medium">Capacity:</span> {{ room.room_capacity }} person(s)
        </p>
        <p class="text-gray-600">
          <span class="font-medium">Floor:</span> {{ room.floor_name }}
        </p>
      </div>
      <button
        @click="$emit('book-room', room)"
        class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition duration-300"
        :disabled="room.status !== 'available'"
        :class="{'opacity-50 cursor-not-allowed': room.status !== 'available'}"
      >
        {{ room.status === 'available' ? 'Book Now' : 'Not Available' }}
      </button>
    </div>
  </div>
</template>

<script setup>
// Props
const props = defineProps({
  room: {
    type: Object,
    required: true
  }
});

// Emits
defineEmits(['book-room']);

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
</script>