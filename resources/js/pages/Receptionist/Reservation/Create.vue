<template>
  <div class="mx-auto max-w-4xl px-4 py-8">
    <div class="rounded-lg bg-gray-900 p-8 text-gray-200 shadow-lg">
      <!-- Header -->
      <h2 class="mb-6 text-3xl font-bold">Create New Reservation</h2>

      <!-- Form -->
      <form @submit.prevent="createReservation" class="space-y-6">
        <!-- Room Selection -->
        <div>
          <Label for="room_id" class="mb-2 block text-sm font-semibold">Room</Label>
          <Select v-model="form.room_id" required>
            <SelectTrigger class="w-full rounded-lg border border-gray-600 bg-gray-800 p-3 focus:ring-2 focus:ring-blue-500">
              <SelectValue placeholder="Select a room" />
            </SelectTrigger>
            <SelectContent class="bg-gray-800 text-gray-200 border-gray-600">
              <SelectItem v-for="room in rooms" :key="room.id" :value="room.id">
                Room {{ room.room_number }} - Capacity: {{ room.room_capacity }} - Price: ${{ room.price }}
              </SelectItem>
            </SelectContent>
          </Select>
          <div v-if="errors.room_id" class="mt-1 text-sm text-red-400">{{ errors.room_id }}</div>
        </div>

        <!-- Client Selection -->
        <div>
          <Label for="client_id" class="mb-2 block text-sm font-semibold">Client</Label>
          <Select v-model="form.client_id" required>
            <SelectTrigger class="w-full rounded-lg border border-gray-600 bg-gray-800 p-3 focus:ring-2 focus:ring-blue-500">
              <SelectValue placeholder="Select a client" />
            </SelectTrigger>
            <SelectContent class="bg-gray-800 text-gray-200 border-gray-600">
              <SelectItem v-for="client in approvedClients" :key="client.id" :value="client.id">
                {{ client.name }} - {{ client.email }}
              </SelectItem>
            </SelectContent>
          </Select>
          <div v-if="errors.client_id" class="mt-1 text-sm text-red-400">{{ errors.client_id }}</div>
        </div>

        <!-- Accompany Number -->
        <div>
          <Label for="accompany_number" class="mb-2 block text-sm font-semibold">Accompany Number</Label>
          <Input
            v-model="form.accompany_number"
            type="number"
            id="accompany_number"
            min="1"
            required
            class="w-full rounded-lg border border-gray-600 bg-gray-800 p-3 focus:ring-2 focus:ring-blue-500"
          />
          <div v-if="errors.accompany_number" class="mt-1 text-sm text-red-400">{{ errors.accompany_number }}</div>
        </div>

        <!-- Price Paid -->
        <div>
          <Label for="price_paid" class="mb-2 block text-sm font-semibold">Price Paid</Label>
          <Input
            v-model="form.price_paid"
            type="number"
            id="price_paid"
            min="0"
            required
            class="w-full rounded-lg border border-gray-600 bg-gray-800 p-3 focus:ring-2 focus:ring-blue-500"
          />
          <div v-if="errors.price_paid" class="mt-1 text-sm text-red-400">{{ errors.price_paid }}</div>
        </div>

        <!-- Check-in Date -->
        <div>
          <Label for="check_in_date" class="mb-2 block text-sm font-semibold">Check-in Date</Label>
          <Input
            v-model="form.check_in_date"
            type="date"
            id="check_in_date"
            required
            class="w-full rounded-lg border border-gray-600 bg-gray-800 p-3 focus:ring-2 focus:ring-blue-500"
          />
          <div v-if="errors.check_in_date" class="mt-1 text-sm text-red-400">{{ errors.check_in_date }}</div>
        </div>

        <!-- Check-out Date -->
        <div>
          <Label for="check_out_date" class="mb-2 block text-sm font-semibold">Check-out Date</Label>
          <Input
            v-model="form.check_out_date"
            type="date"
            id="check_out_date"
            required
            class="w-full rounded-lg border border-gray-600 bg-gray-800 p-3 focus:ring-2 focus:ring-blue-500"
          />
          <div v-if="errors.check_out_date" class="mt-1 text-sm text-red-400">{{ errors.check_out_date }}</div>
        </div>

        <!-- Status -->
        <div>
          <Label for="status" class="mb-2 block text-sm font-semibold">Status</Label>
          <Select v-model="form.status" required>
            <SelectTrigger class="w-full rounded-lg border border-gray-600 bg-gray-800 p-3 focus:ring-2 focus:ring-blue-500">
              <SelectValue placeholder="Select status" />
            </SelectTrigger>
            <SelectContent class="bg-gray-800 text-gray-200 border-gray-600">
              <SelectItem value="pending">Pending</SelectItem>
              <SelectItem value="confirmed">Confirmed</SelectItem>
              <SelectItem value="checked_in">Checked In</SelectItem>
              <SelectItem value="checked_out">Checked Out</SelectItem>
              <SelectItem value="cancelled">Cancelled</SelectItem>
            </SelectContent>
          </Select>
          <div v-if="errors.status" class="mt-1 text-sm text-red-400">{{ errors.status }}</div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end space-x-3">
          <Button @click="goBack" variant="outline" type="button" class="rounded-lg border border-gray-600 bg-gray-800 px-6 py-3 font-semibold text-gray-200 hover:bg-gray-700">
            Cancel
          </Button>
          <Button type="submit" class="rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white transition hover:bg-blue-700">
            Create Reservation
          </Button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select/index';

// Props
const props = defineProps({
  rooms: {
    type: Array,
    required: true
  },
  approvedClients: {
    type: Array,
    required: true
  }
});

// State
const errors = ref({});

// Form
const form = useForm({
  room_id: '',
  client_id: '',
  accompany_number: 1,
  price_paid: 0,
  check_in_date: new Date().toISOString().split('T')[0], // Today's date
  check_out_date: new Date(Date.now() + 86400000).toISOString().split('T')[0], // Tomorrow's date
  status: 'pending'
});

// Methods
const createReservation = () => {
  form.post(route('receptionist.reservations.store'), {
    onSuccess: () => {
      router.visit(route('receptionist.reservations.index'), {
        preserveScroll: true
      });
    },
    onError: (e) => {
      errors.value = e;
    }
  });
};

const goBack = () => {
  router.visit(route('receptionist.reservations.index'), {
    preserveScroll: true
  });
};
</script>

<style scoped>
label {
  color: #e5e7eb;
}
</style>