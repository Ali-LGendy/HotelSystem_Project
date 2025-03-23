<template>
  <div class="mx-auto max-w-7xl px-4 py-8">
    <div class="rounded-lg bg-gray-900 p-8 text-gray-200 shadow-lg">
      <!-- Header with Navigation -->
      <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <h2 class="text-3xl font-bold">Reservations Management</h2>
        <div class="flex flex-wrap gap-3">
          <Link
            v-for="(link, index) in navigationLinks"
            :key="index"
            :href="link.href"
            class="rounded-lg px-4 py-2 font-semibold transition"
            :class="link.bgClass"
            preserve-scroll
          >
            {{ link.text }}
          </Link>
        </div>
      </div>

      <!-- Data Table -->
      <div class="rounded-lg border border-gray-700 bg-gray-800 overflow-hidden">
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
                @click="approveReservation(row)"
                class="bg-green-700 hover:bg-green-600"
              >
                Approve
              </Button>
              <Button
                variant="outline"
                size="sm"
                @click="viewReservation(row)"
                class="border-gray-600 bg-gray-700 text-gray-200 hover:bg-gray-600"
              >
                View
              </Button>
              <Button
                variant="outline"
                size="sm"
                @click="editReservation(row)"
                class="border-gray-600 bg-gray-700 text-gray-200 hover:bg-gray-600"
              >
                Edit
              </Button>
              <Button
                variant="destructive"
                size="sm"
                @click="confirmDelete(row)"
                class="bg-red-800 hover:bg-red-700"
              >
                Delete
              </Button>
            </div>
          </template>
        </DataTable>
      </div>

      <!-- Delete Confirmation Dialog -->
      <Dialog :open="showDeleteDialog" @close="showDeleteDialog = false">
        <DialogContent class="bg-gray-800 text-gray-200 border-gray-700">
          <DialogHeader>
            <DialogTitle class="text-gray-100">Confirm Deletion</DialogTitle>
            <DialogDescription class="text-gray-400">
              Are you sure you want to delete this reservation? This action cannot be undone.
            </DialogDescription>
          </DialogHeader>
          <div class="mt-6 flex justify-end space-x-3">
            <Button
              variant="outline"
              @click="showDeleteDialog = false"
              class="border-gray-600 bg-gray-700 text-gray-200 hover:bg-gray-600"
            >
              Cancel
            </Button>
            <Button
              variant="destructive"
              @click="deleteReservation"
              class="bg-red-700 hover:bg-red-600"
            >
              Delete
            </Button>
          </div>
        </DialogContent>
      </Dialog>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import DataTable from '@/components/ui/DataTable.vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';

// Props
const props = defineProps({
  reservations: {
    type: Object,
    required: true
  }
});

// State
const showDeleteDialog = ref(false);
const selectedReservation = ref(null);

// Navigation Links
const navigationLinks = [
  {
    href: route('receptionist.clients.pending'),
    text: 'Pending Clients',
    bgClass: 'bg-yellow-600 text-white hover:bg-yellow-700'
  },
  {
    href: route('receptionist.clients.approved'),
    text: 'My Approved Clients',
    bgClass: 'bg-green-600 text-white hover:bg-green-700'
  },
  {
    href: route('receptionist.clients.reservations'),
    text: 'Clients Reservations',
    bgClass: 'bg-indigo-600 text-white hover:bg-indigo-700'
  },
  {
    href: route('receptionist.reservations.create'),
    text: 'New Reservation',
    bgClass: 'bg-blue-600 text-white hover:bg-blue-700'
  }
];

// Handle navigation button clicks
const handleNavigation = (route) => {
  router.visit(route, {
    preserveScroll: true,
    onError: (errors) => {
      console.error('Navigation error:', errors);
    }
  });
};

// Table Columns
const columns = [
  {
    accessorKey: 'client.name', // Changed from user.name to client.name
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
    accessorKey: 'price_paid', // Using the correct column name from the database
    header: 'Paid Price',
    cell: ({ row }) => row.original && row.original.price_paid ? `$${row.original.price_paid}` : 'N/A'
  },
  {
    accessorKey: 'check_in_date',
    header: 'Check-in Date',
    cell: ({ row }) => row.original ? formatDate(row.original.check_in_date) : 'N/A'
  },
  {
    accessorKey: 'check_out_date',
    header: 'Check-out Date',
    cell: ({ row }) => row.original ? formatDate(row.original.check_out_date) : 'N/A'
  },
  {
    accessorKey: 'status',
    header: 'Status'
  },
  {
    id: 'actions',
    header: 'Actions'
  }
];

// Computed
const currentPage = computed(() => {
  return props.reservations.current_page || 1;
});

// Methods
const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  try {
    const date = new Date(dateString);
    return date.toLocaleDateString();
  } catch (e) {
    return dateString;
  }
};

const getStatusVariant = (status) => {
  const variants = {
    'confirmed': 'success',
    'checked_in': 'info',
    'checked-in': 'info',     // Support both formats for backward compatibility
    'checked_out': 'secondary',
    'checked-out': 'secondary', // Support both formats for backward compatibility
    'pending': 'warning',
    'cancelled': 'destructive'
  };
  return variants[status] || 'default';
};

const handlePageChange = (pageIndex) => {
  router.visit(
    route('receptionist.reservations.index', { page: pageIndex + 1 }),
    {
      only: ['reservations'],
      preserveState: true,
      preserveScroll: true
    }
  );
};

const viewReservation = (reservation) => {
  console.log('Viewing reservation:', reservation);
  // Ensure we get the correct ID regardless of data structure
  const id = reservation.original ? reservation.original.id : reservation.id;

  // Use router.visit for navigation
  router.visit(route('receptionist.reservations.show', id), {
    preserveScroll: true,
    onError: (errors) => {
      console.error('Error viewing reservation:', errors);
    }
  });
};

const editReservation = (reservation) => {
  console.log('Editing reservation:', reservation);
  // Ensure we get the correct ID regardless of data structure
  const id = reservation.original ? reservation.original.id : reservation.id;

  // Use router.visit for navigation
  router.visit(route('receptionist.reservations.edit', id), {
    preserveScroll: true,
    onError: (errors) => {
      console.error('Error editing reservation:', errors);
    }
  });
};

const confirmDelete = (reservation) => {
  // Store the reservation object with the correct structure
  selectedReservation.value = reservation.original || reservation;
  showDeleteDialog.value = true;
};

const deleteReservation = () => {
  console.log('Deleting reservation:', selectedReservation.value);
  if (selectedReservation.value) {
    const id = selectedReservation.value.id;

    // Use router.delete with proper error handling
    router.delete(route('receptionist.reservations.destroy', id), {
      onSuccess: () => {
        showDeleteDialog.value = false;
        selectedReservation.value = null;

        // Refresh the page to show updated data
        router.visit(route('receptionist.reservations.index'), {
          only: ['reservations'],
          preserveScroll: true
        });
      },
      onError: (errors) => {
        console.error('Error deleting reservation:', errors);
        showDeleteDialog.value = false;
      },
      preserveScroll: true
    });
  }
};

const approveReservation = (reservation) => {
  console.log('Approving reservation:', reservation);
  // Ensure we get the correct ID regardless of data structure
  const id = reservation.original ? reservation.original.id : reservation.id;
  const data = {
    status: 'confirmed',
    // Include other required fields from the reservation
    room_id: reservation.original.room_id,
    accompany_number: reservation.original.accompany_number,
    check_in_date: reservation.original.check_in_date,
    check_out_date: reservation.original.check_out_date,
    price_paid: reservation.original.price_paid
  };

  // Use router.put to update the status
  router.put(route('receptionist.reservations.update', id), data, {
    onSuccess: () => {
      // Refresh the page to show updated data
      router.visit(route('receptionist.reservations.index'), {
        only: ['reservations'],
        preserveScroll: true
      });
    },
    onError: (errors) => {
      console.error('Error approving reservation:', errors);
    },
    preserveScroll: true
  });
};
</script>

<style scoped>
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
