<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import axios from 'axios';
import { useToast } from '@/composables/useToast';
import { useReservationManagement } from '@/composables/useReservationManagement';
import ReceptionistLayout from '@/layouts/ReceptionistLayout.vue';

// UI Components
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import DataTable from '@/components/ui/DataTable.vue';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle
} from '@/components/ui/dialog';
import ConfirmActionDialog from '@/components/receptionist/ConfirmActionDialog.vue';

defineOptions({ layout: ReceptionistLayout });

// Initialize toast
const toast = useToast();

// Props
const props = defineProps({
  reservations: {
    type: Object,
    required: true
  },
  reservationStats: {
    type: Object,
    default: () => ({
      totalPending: 0,
      confirmedReservations: 0,
      checkedInGuests: 0
    })
  }
});

// Get reservation management functions
const {
  processing,
  showConfirmDialog,
  confirmDialogTitle,
  confirmDialogMessage,
  confirmAction,
  selectedReservation,
  openApproveDialog,
  cancelConfirmation,
  handleConfirmAction
} = useReservationManagement();

// State
const showDeleteDialog = ref(false);
const selectedReservationForDelete = ref(null);
const currentSort = ref({
  field: 'created_at',
  direction: 'desc'
});
const perPage = ref(10);

// Table Columns
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
  const page = pageIndex + 1;
  sortAndPaginate(page, perPage.value, currentSort.value.field, currentSort.value.direction);
};

// Enhanced pagination with sorting
const sortAndPaginate = (page = 1, perPage = 10, sortBy = 'created_at', sortDir = 'desc') => {
  // Update current sort state
  currentSort.value = {
    field: sortBy,
    direction: sortDir
  };

  // Navigate with new parameters using Inertia's get method
  router.get('/receptionist/reservations', {
    page,
    per_page: perPage,
    sort_by: sortBy,
    sort_dir: sortDir
  }, {
    preserveScroll: true,
    preserveState: true, // Keep component state between requests
    only: ['reservations', 'reservationStats'], // Only refresh these data props
    replace: true // Replace current history entry instead of adding a new one
  });
};

const confirmDelete = (reservation) => {
  // Store the reservation object with the correct structure
  selectedReservation.value = reservation.original || reservation;
  showDeleteDialog.value = true;
};

const cancelDelete = () => {
  showDeleteDialog.value = false;
  selectedReservation.value = null;
};

// Navigation method using Inertia
const navigateTo = (url) => {
  router.visit(url, {
    preserveScroll: false,
    preserveState: false,
    replace: false
  });
};

const deleteReservation = () => {
  if (selectedReservation.value) {
    try {
      const id = selectedReservation.value.id;

      // Store reservation details before deletion for success message
      const clientName = selectedReservation.value && selectedReservation.value.client ?
        selectedReservation.value.client.name : 'Client';
      const roomNumber = selectedReservation.value && selectedReservation.value.room ?
        selectedReservation.value.room.room_number : '';

      // Close the dialog immediately to prevent UI issues
      showDeleteDialog.value = false;

      // Use Inertia's delete method with proper callbacks
      router.delete(route('receptionist.reservations.destroy', { reservation: id }), {}, {
        onSuccess: () => {
          // Create a descriptive success message
          let description = `The reservation has been permanently removed from the system.`;
          if (clientName && roomNumber) {
            description = `${clientName}'s reservation for Room ${roomNumber} has been permanently removed from the system.`;
          } else if (clientName) {
            description = `${clientName}'s reservation has been permanently removed from the system.`;
          }

          toast.success('Reservation Deleted Successfully!', {
            description: description,
            duration: 6000,
            position: 'top-center'
          });

          selectedReservation.value = null;
        },
        onError: (errors) => {
          console.error('Error deleting reservation:', errors);

          let errorMessage = 'Could not delete reservation. Please try again.';
          if (errors && typeof errors === 'object') {
            const errorValues = Object.values(errors).flat();
            if (errorValues.length > 0) {
              errorMessage = errorValues.join('. ');
            }
          }

          toast.error('Deletion Failed', {
            description: errorMessage,
            duration: 6000
          });

          // Reopen the dialog if there was an error
          showDeleteDialog.value = true;
        },
        onFinish: () => {
          // Additional cleanup if needed
        }
      });
    } catch (error) {
      console.error('Exception in deleteReservation:', error);

      toast.error('Deletion Failed', {
        description: 'An unexpected error occurred while trying to delete the reservation. Please try again or contact support.',
        duration: 6000
      });

      showDeleteDialog.value = false;
    }
  } else {
    showDeleteDialog.value = false;
  }
};

// Use the openApproveDialog function from useReservationManagement
const approveReservation = (row) => {
  // Get the reservation data, handling both possible structures
  let reservation;

  if (row.original) {
    reservation = row.original;
  } else {
    reservation = row;
  }

  if (!reservation || !reservation.id) {
    console.error('Invalid reservation object:', reservation);
    toast.error('Error: Could not identify reservation. Please try again.');
    return;
  }

  // Open the confirmation dialog
  openApproveDialog(reservation);
};
</script>

<template>
  <div class="min-h-screen bg-background text-foreground p-8">
    <div class="rounded-lg border border-border bg-card p-8 shadow-sm">
      <!-- Header with Navigation -->
      <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h1 class="text-3xl font-bold">Pending Reservations</h1>
          <p class="mt-2 text-muted-foreground">Showing reservations that need your approval</p>
        </div>
        <div class="flex flex-wrap gap-3">
          <Button
            @click="navigateTo('/receptionist/clients/my-approved')"
            variant="outline"
          >
            My Approved Clients
          </Button>
          <Button
            @click="navigateTo('/receptionist/clients/reservations')"
            variant="default"
          >
            Clients Reservations
          </Button>
          <Button
            @click="navigateTo('/receptionist/clients')"
            variant="default"
          >
            Manage Clients
          </Button>
        </div>
      </div>

      <!-- No Reservations Message -->
      <div v-if="reservations.data.length === 0" class="text-center text-muted-foreground py-8 border rounded-lg">
        <p class="text-lg">No pending reservations found.</p>
      </div>

      <!-- Data Table -->
      <div v-else class="rounded-lg border border-border overflow-hidden">
        <DataTable
          :columns="columns"
          :data="reservations.data"
          :pagination="{
            pageSize: perPage,
            pageIndex: currentPage - 1,
            totalItems: reservations.total,
            manualPagination: true
          }"
          @page-change="handlePageChange"
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
                variant="default"
                size="sm"
                class="bg-green-600 hover:bg-green-700"
                @click.prevent="approveReservation(row)"
              >
                Approve
              </Button>
              <Button
                @click="navigateTo('/receptionist/reservations/' + (row.original ? row.original.id : row.id))"
                variant="outline"
                size="sm"
              >
                View
              </Button>
              <Button
                @click="navigateTo('/receptionist/reservations/' + (row.original ? row.original.id : row.id) + '/edit')"
                variant="outline"
                size="sm"
              >
                Edit
              </Button>
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

        <!-- Enhanced Pagination -->
        <div v-if="reservations.data.length > 0" class="mt-4 flex justify-between items-center p-4 border-t border-border">
          <div class="text-sm text-muted-foreground">
            Showing {{ reservations.from }} to {{ reservations.to }} of {{ reservations.total }} reservations
          </div>

          <!-- Page Size Selector and Navigation -->
          <div class="flex items-center gap-4">
            <div class="flex items-center">
              <span class="text-sm text-muted-foreground mr-2">Per page:</span>
              <select
                v-model="perPage"
                @change="sortAndPaginate(1, perPage, currentSort.field, currentSort.direction)"
                class="h-9 w-20 rounded-md border border-border bg-card px-3 py-1 text-sm shadow-sm"
              >
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
              </select>
            </div>

            <!-- Page Navigation -->
            <div class="flex gap-2">
              <Button
                v-for="page in reservations.links"
                :key="page.label"
                @click="page.url && sortAndPaginate(
                  page.label === '&laquo; Previous' ? reservations.current_page - 1 :
                  page.label === 'Next &raquo;' ? reservations.current_page + 1 :
                  parseInt(page.label),
                  perPage,
                  currentSort.field,
                  currentSort.direction
                )"
                :disabled="!page.url"
                :variant="page.active ? 'default' : 'outline'"
                size="sm"
                v-html="page.label"
              ></Button>
            </div>
          </div>
        </div>
      </div>

      <!-- Delete Confirmation Dialog -->
      <Dialog :open="showDeleteDialog" @update:open="(value) => value ? null : cancelDelete()">
        <DialogContent @pointerDownOutside="cancelDelete">
          <DialogHeader>
            <DialogTitle>Confirm Deletion</DialogTitle>
            <DialogDescription>
              Are you sure you want to delete this reservation? This action cannot be undone.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button
              variant="outline"
              @click="cancelDelete"
              type="button"
            >
              Cancel
            </Button>
            <Button
              variant="destructive"
              @click="deleteReservation"
              type="button"
            >
              Delete
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <!-- Reservation Statistics Dashboard -->
      <div class="mt-8 p-6 rounded-lg border border-border bg-accent/10">
        <h2 class="text-xl font-semibold mb-4">Reservation Statistics</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          <div class="p-4 rounded-lg border border-border bg-card shadow-sm">
            <div class="text-sm text-muted-foreground">Total Pending Reservations</div>
            <div class="text-2xl font-bold">{{ reservationStats.totalPending }}</div>
          </div>
          <div class="p-4 rounded-lg border border-blue-600/20 bg-blue-950/10 shadow-sm">
            <div class="text-sm text-muted-foreground">Confirmed Reservations</div>
            <div class="text-2xl font-bold">{{ reservationStats.confirmedReservations }}</div>
          </div>
          <div class="p-4 rounded-lg border border-green-600/20 bg-green-950/10 shadow-sm">
            <div class="text-sm text-muted-foreground">Checked-in Guests</div>
            <div class="text-2xl font-bold">{{ reservationStats.checkedInGuests }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Confirmation Dialog -->
  <ConfirmActionDialog
    :show="showConfirmDialog"
    :title="confirmDialogTitle"
    :message="confirmDialogMessage"
    :action-type="confirmAction"
    :processing="processing"
    @confirm="handleConfirmAction"
    @cancel="cancelConfirmation"
  />
</template>
