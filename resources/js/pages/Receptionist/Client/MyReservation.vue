<template>
    <div class="min-h-screen bg-background px-8 py-12 text-foreground">
      <div class="mb-6 flex items-center justify-between">
        <h2 class="text-2xl font-bold">My Reservations</h2>
      </div>
  
      <div v-if="reservations.length === 0" class="rounded-lg bg-accent/50 p-6 text-center text-muted-foreground">
        No reservations found.
      </div>
  
      <div v-else class="overflow-x-auto">
        <table class="w-full table-auto text-sm">
          <thead>
            <tr class="border-b border-border text-xs uppercase tracking-wider text-muted-foreground">
              <th class="px-4 py-3 text-left">Accompany #</th>
              <th class="px-4 py-3 text-left">Room #</th>
              <th class="px-4 py-3 text-right">Paid</th>
              <th class="px-4 py-3 text-right">Status</th>
              <th class="px-4 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="reservation in reservations"
              :key="reservation.id"
              class="border-b border-border transition hover:bg-muted/30"
            >
              <td class="whitespace-nowrap px-4 py-3 font-medium">
                {{ reservation.accompany_number }}
              </td>
              <td class="whitespace-nowrap px-4 py-3">{{ reservation.room_id }}</td>
              <td class="whitespace-nowrap px-4 py-3 text-right">
                ${{ (reservation.price_paid / 100).toFixed(2) }}
              </td>
              <td class="whitespace-nowrap px-4 py-3 text-right">
                <span
                  :class="getStatusBadgeClass(reservation.status)"
                  class="inline-block rounded-full px-2 py-1 text-xs font-medium"
                >
                  {{ reservation.status }}
                </span>
              </td>
              <td class="whitespace-nowrap px-4 py-3 text-right">
                <Button
                  v-if="reservation.status === 'pending'"
                  size="sm"
                  @click="goToStripe(reservation.id)"
                >
                  Pay Now
                </Button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
  
      <div v-if="pagination" class="mt-6 flex justify-center">
        <Pagination :pagination="pagination" />
      </div>
    </div>
</template>
  
<script setup>
  import Pagination from '@/Components/Pagination.vue'
  import { Button } from '@/components/ui/button'
  
  const props = defineProps({
    reservations: {
      type: Array,
      default: () => [],
    },
    pagination: {
      type: Object,
      default: null,
    },
  })
  
  const goToStripe = (reservationId) => {
    window.location.href = `/reservations/checkout/${reservationId}`
  }
  
  const getStatusBadgeClass = (status) => {
    switch (status) {
      case 'completed':
        return 'bg-green-200 text-green-800 dark:bg-green-800 dark:text-green-100'
      case 'pending':
        return 'bg-yellow-200 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100'
      case 'cancelled':
        return 'bg-red-200 text-red-800 dark:bg-red-700 dark:text-red-100'
      default:
        return 'bg-muted text-foreground'
    }
  }
</script>
  