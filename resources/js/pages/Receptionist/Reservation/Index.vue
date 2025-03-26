<template>
  <div class="mx-auto max-w-7xl px-4 py-8">
    <div class="rounded-lg bg-gray-900 p-8 text-gray-200 shadow-lg">
      <!-- Header with Navigation -->
      <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h2 class="text-3xl font-bold">{{ showAll ? 'All Reservations' : 'Pending Reservations' }}</h2>
          <p class="mt-2 text-gray-400">{{ showAll ? 'Showing all reservations in the system' : 'Showing reservations that need your approval' }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
          <Link
            v-if="!showAll"
            :href="route('receptionist.all-reservations')"
            class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white transition hover:bg-indigo-700"
          >
            All Reservations
          </Link>
          <Link
            v-else
            :href="route('receptionist.reservations')"
            class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white transition hover:bg-indigo-700"
          >
            Pending Reservations
          </Link>
          <Link
            :href="route('receptionist.clients.approved')"
            class="rounded-lg bg-green-600 px-4 py-2 font-semibold text-white transition hover:bg-green-700"
          >
            My Approved Clients
          </Link>
        </div>
      </div>

      <!-- No Reservations Message -->
      <div v-if="reservations.data.length === 0" class="text-center py-8">
        <p class="text-lg text-gray-300">
          {{ showAll ? 'No reservations found.' : 'No pending reservations found.' }}
        </p>
      </div>

      <!-- Reservations Table -->
      <div v-else class="rounded-lg border border-gray-700 bg-gray-800 overflow-hidden">
        <DataTable
          :columns="columns"
          :data="reservations.data"
          :pagination="{
            pageSize: 10,
            pageIndex: currentPage - 1,
            totalItems: reservations.total,
            manualPagination: true
          }"
          @page-change="handlePageChange"
          class="text-gray-200"
        >
          <!-- Status Cell Template -->
          <template #cell-status="{ row }">
            <Badge :variant="getStatusVariant(row.status)" class="text-xs font-medium">
              {{ row.status }}
            </Badge>
          </template>

          <!-- Actions Cell Template -->
          <template #cell-actions="{ row }">
            <div class="flex items-center gap-2">
              <Button
                v-if="row.original && row.original.status === 'pending'"
                variant="success"
                size="sm"
                @click.prevent="approveReservation(row)"
              >
                Approve
              </Button>
              <Link
                :href="route('receptionist.reservations.show', { reservation: row.original ? row.original.id : row.id })"
                class="rounded-md border border-gray-600 bg-gray-700 px-3 py-1 text-sm font-medium text-gray-200 hover:bg-gray-600"
              >
                View
              </Link>
              <Link
                :href="route('receptionist.reservations.edit', { reservation: row.original ? row.original.id : row.id })"
                class="rounded-md border border-gray-600 bg-gray-700 px-3 py-1 text-sm font-medium text-gray-200 hover:bg-gray-600"
              >
                Edit
              </Link>
              <Button
                variant="destructive"
                size="sm"
                @click="confirmDelete(row)"
              >
                Delete
              </Button>
            </div>
          </template>
        </DataTable>
      </div>

      <!-- Delete Confirmation Dialog -->
      <Dialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Confirm Deletion</DialogTitle>
            <DialogDescription>
              Are you sure you want to delete this reservation? This action cannot be undone.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button variant="outline" @click="showDeleteDialog = false">
              Cancel
            </Button>
            <Button variant="destructive" @click="deleteReservation">
              Delete
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

// Shadcn UI Components
import { Link } from '@/Components/ui/link'
import { Button } from '@/Components/ui/button'
import { 
  Dialog, 
  DialogContent, 
  DialogDescription, 
  DialogFooter, 
  DialogHeader, 
  DialogTitle 
} from '@/Components/ui/dialog'
import { Badge } from '@/Components/ui/badge'

// Custom Components
import DataTable from '@/Components/ui/DataTable.vue'

// Props Definition
const props = defineProps({
  reservations: {
    type: Object,
    required: true
  },
  showAll: {
    type: Boolean,
    default: false
  }
})

// State Management
const showDeleteDialog = ref(false)
const selectedReservation = ref(null)

// Computed Properties
const currentPage = computed(() => {
  return props.reservations.current_page || 1
})

// Table Columns Configuration
const columns = [
  {
    accessorKey: 'client.name',
    header: 'Client Name'
  },
  {
    accessorKey: 'room.room_number',
    header: 'Room Number'
  },
  {
    accessorKey: 'accompany_number',
    header: 'Accompany Number'
  },
  {
    accessorKey: 'price_paid',
    header: 'Paid Price',
    cell: ({ row }) => row.original && row.original.price_paid 
      ? `$${row.original.price_paid}` 
      : 'N/A'
  },
  {
    accessorKey: 'check_in_date',
    header: 'Check-in Date',
    cell: ({ row }) => row.original 
      ? formatDate(row.original.check_in_date) 
      : 'N/A'
  },
  {
    accessorKey: 'check_out_date',
    header: 'Check-out Date',
    cell: ({ row }) => row.original 
      ? formatDate(row.original.check_out_date) 
      : 'N/A'
  },
  {
    accessorKey: 'status',
    header: 'Status'
  },
  {
    id: 'actions',
    header: 'Actions'
  }
]

// Utility Functions
const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  try {
    const date = new Date(dateString)
    return date.toLocaleDateString()
  } catch (e) {
    return dateString
  }
}

const getStatusVariant = (status) => {
  const variants = {
    'confirmed': 'success',
    'checked_in': 'info',
    'checked-in': 'info',
    'checked_out': 'secondary',
    'checked-out': 'secondary',
    'pending': 'warning',
    'cancelled': 'destructive'
  }
  return variants[status] || 'default'
}

// Pagination Handler
const handlePageChange = (pageIndex) => {
  const page = pageIndex + 1
  const url = props.showAll
    ? route('receptionist.all-reservations', { page })
    : route('receptionist.reservations', { page })

  router.visit(url, {
    preserveScroll: true,
    preserveState: true,
    replace: true
  })
}

// Reservation Management Methods
const confirmDelete = (reservation) => {
  selectedReservation.value = reservation.original || reservation
  showDeleteDialog.value = true
}

const deleteReservation = () => {
  if (selectedReservation.value) {
    router.delete(
      route('receptionist.reservations.destroy', { 
        reservation: selectedReservation.value.id 
      }),
      {
        onSuccess: () => {
          showDeleteDialog.value = false
          selectedReservation.value = null
          // Optional: Add toast notification
        },
        onError: (errors) => {
          console.error('Error deleting reservation:', errors)
          // Optional: Add error toast notification
        }
      }
    )
  }
}

const approveReservation = (row) => {
  const reservation = row.original || row

  if (!reservation || !reservation.id) {
    console.error('Invalid reservation object')
    return
  }

  const data = {
    status: 'confirmed',
    ...reservation  // Spread other existing reservation details
  }

  router.put(
    route('receptionist.reservations.update', { 
      reservation: reservation.id 
    }), 
    data,
    {
      onSuccess: () => {
        // Optional: Add success toast notification
      },
      onError: (errors) => {
        console.error('Reservation approval error:', errors)
        // Optional: Add error toast notification
      }
    }
  )
}
</script>

<style scoped>
/* Existing custom styles */
label {
  color: #e5e7eb;
}

:deep(.data-table) {
  --background: #1f2937;
  --text: #e5e7eb;
  --border: #374151;
  --header-background: #111827;
  --header-text: #f9fafb;
  --row-hover: #2d3748;
}

:deep(.data-table th) {
  background-color: #111827;
  color: #f9fafb;
  font-weight: 600;
}

:deep(.data-table td) {
  border-color: #374151;
}

:deep(.data-table tr:hover td) {
  background-color: #2d3748;
}
</style>