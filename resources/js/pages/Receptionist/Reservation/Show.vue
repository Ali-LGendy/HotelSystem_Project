<template>
  <div class="mx-auto max-w-4xl px-4 py-8">
    <div class="rounded-lg bg-gray-900 p-8 text-gray-200 shadow-lg">
      <!-- Header -->
      <h2 class="mb-6 text-3xl font-bold">Reservation Details</h2>

      <!-- Details -->
      <div class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="rounded-md bg-gray-800 p-4">
            <h3 class="text-sm font-medium text-gray-400">Client Name</h3>
            <p class="mt-1 text-lg font-semibold">{{ reservation.client.name }}</p>
          </div>

          <div class="rounded-md bg-gray-800 p-4">
            <h3 class="text-sm font-medium text-gray-400">Room Number</h3>
            <p class="mt-1 text-lg font-semibold">{{ reservation.room.room_number }}</p>
          </div>

          <div class="rounded-md bg-gray-800 p-4">
            <h3 class="text-sm font-medium text-gray-400">Accompany Number</h3>
            <p class="mt-1 text-lg font-semibold">{{ reservation.accompany_number }}</p>
          </div>

          <div class="rounded-md bg-gray-800 p-4">
            <h3 class="text-sm font-medium text-gray-400">Check-in Date</h3>
            <p class="mt-1 text-lg font-semibold">{{ formatDate(reservation.check_in_date) }}</p>
          </div>

          <div class="rounded-md bg-gray-800 p-4">
            <h3 class="text-sm font-medium text-gray-400">Check-out Date</h3>
            <p class="mt-1 text-lg font-semibold">{{ formatDate(reservation.check_out_date) }}</p>
          </div>

          <div class="rounded-md bg-gray-800 p-4">
            <h3 class="text-sm font-medium text-gray-400">Status</h3>
            <div class="mt-1">
              <Badge :variant="getStatusVariant(reservation.status)" class="text-sm font-medium">
                {{ reservation.status }}
              </Badge>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="mt-8 flex justify-end space-x-4">
        <!-- Status Change Buttons -->
        <div v-if="reservation.status === 'pending'">
          <Button
            @click="updateStatus('confirmed')"
            class="rounded-lg bg-green-600 px-6 py-3 font-semibold text-white transition hover:bg-green-700"
          >
            Approve Reservation
          </Button>
        </div>
        <div v-if="reservation.status === 'confirmed'">
          <Button
            @click="updateStatus('checked_in')"
            class="rounded-lg bg-green-600 px-6 py-3 font-semibold text-white transition hover:bg-green-700"
          >
            Check In Guest
          </Button>
        </div>
        <div v-if="reservation.status === 'checked_in'">
          <Button
            @click="updateStatus('checked_out')"
            class="rounded-lg bg-purple-600 px-6 py-3 font-semibold text-white transition hover:bg-purple-700"
          >
            Check Out Guest
          </Button>
        </div>
        <Button
          @click="editReservation"
          class="rounded-lg bg-yellow-600 px-6 py-3 font-semibold text-white transition hover:bg-yellow-700"
        >
          Edit Reservation
        </Button>
        <Button
          @click="goBack"
          class="rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white transition hover:bg-blue-700"
        >
          Back to Reservations
        </Button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue';
import { router } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

const props = defineProps({
  reservation: {
    type: Object,
    required: true
  }
});

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  try {
    const date = new Date(dateString);
    return date.toLocaleDateString();
  } catch (e) {
    return dateString;
  }
};

const goBack = () => {
  router.visit(route('receptionist.reservations.index'), {
    preserveScroll: true
  });
};

const editReservation = () => {
  router.visit(route('receptionist.reservations.edit', props.reservation.id), {
    preserveScroll: true
  });
};

const updateStatus = (newStatus) => {
  // Create a data object with just the status change
  const data = {
    status: newStatus,
    // Include other required fields from the reservation
    room_id: props.reservation.room_id,
    accompany_number: props.reservation.accompany_number,
    check_in_date: props.reservation.check_in_date,
    check_out_date: props.reservation.check_out_date,
    price_paid: props.reservation.price_paid,
    // Add redirect_back parameter to tell the controller to redirect back to this page
    redirect_back: true
  };

  // Use router.put to update just the status
  router.put(route('receptionist.reservations.update', props.reservation.id), data, {
    onSuccess: () => {
      // Reload the current page to show the updated status
      router.reload({
        preserveScroll: true
      });
    },
    onError: (errors) => {
      console.error('Error updating reservation status:', errors);
    },
    preserveScroll: true
  });
};

const getStatusVariant = (status) => {
  const variants = {
    confirmed: 'success',
    'checked_in': 'info',
    'checked-in': 'info',     // Support both formats for backward compatibility
    'checked_out': 'secondary',
    'checked-out': 'secondary', // Support both formats for backward compatibility
    pending: 'warning',
    cancelled: 'destructive'
  };
  return variants[status] || 'default';
};
</script>

<style scoped>
label {
  color: #e5e7eb;
}
</style>
