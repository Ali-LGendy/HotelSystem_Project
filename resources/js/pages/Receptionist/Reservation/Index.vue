<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import ReceptionistLayout from '@/layouts/ReceptionistLayout.vue';
import { useToast } from '@/composables/useToast';

// Shadcn UI Components
import { Link } from '@/Components/ui/link';
import { Button } from '@/Components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle
} from '@/Components/ui/dialog';
import { Badge } from '@/Components/ui/badge';

// Custom Components
import DataTable from '@/Components/ui/DataTable.vue';

defineOptions({ layout: ReceptionistLayout });

// Initialize toast
const toast = useToast();

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
});

// State Management
const showDeleteDialog = ref(false);
const selectedReservation = ref(null);

// Computed Properties
const currentPage = computed(() => {
  return props.reservations.current_page || 1;
});

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
];

// Utility Functions
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
    'checked-in': 'info',
    'checked_out': 'secondary',
    'checked-out': 'secondary',
    'pending': 'warning',
    'cancelled': 'destructive'
  };
  return variants[status] || 'default';
};

// Pagination Handler
const handlePageChange = (pageIndex) => {
  const page = pageIndex + 1;
  const url = props.showAll
    ? route('receptionist.all-reservations', { page })
    : route('receptionist.reservations', { page });

  router.visit(url, {
    preserveScroll: true,
    preserveState: true,
    replace: true
  });
};

// Reservation Management Methods
const confirmDelete = (reservation) => {
  selectedReservation.value = reservation.original || reservation;
  showDeleteDialog.value = true;
};

const deleteReservation = () => {
  if (selectedReservation.value) {
    router.delete(
      route('receptionist.reservations.destroy', {
        reservation: selectedReservation.value.id
      }),
      {
        onSuccess: () => {
          showDeleteDialog.value = false;
          selectedReservation.value = null;
          toast.success('Reservation deleted successfully');
        },
        onError: (errors) => {
          console.error('Error deleting reservation:', errors);
          toast.error('Failed to delete reservation');
        }
      }
    );
  }
};

const viewReservation = (id) => {
  router.visit(route('receptionist.reservations.show', { reservation: id }), {
    preserveScroll: false,
    preserveState: false
  });
};

const editReservation = (id) => {
  router.visit(route('receptionist.reservations.edit', { reservation: id }), {
    preserveScroll: false,
    preserveState: false
  });
};

const approveReservation = async (row) => {
  try {
    const reservation = row.original || row;

    if (!reservation || !reservation.id) {
      console.error('Invalid reservation object');
      return;
    }

    const data = {
      status: 'confirmed',
      ...reservation  // Spread other existing reservation details
    };

    // Use axios directly instead of Inertia to handle the JSON response
    const response = await axios.put(`/receptionist/reservations/${reservation.id}`, data, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });

    // Check if the request was successful
    if (response.data.success) {
      toast.success('Reservation approved successfully!');

      // Check if client is already approved
      const clientApproved = response.data.client_approved;

      // Refresh the current page to show updated data
      if (reservation.client_id && !clientApproved) {
        // If client is not approved, redirect to clients page
        router.visit('/receptionist/clients', {
          preserveScroll: false,
          preserveState: false,
          replace: false
        });
      } else {
        // Otherwise, refresh the current page with updated data
        window.location.reload(); // Full page reload to ensure data is refreshed
      }
    } else {
      toast.error('Could not approve reservation. Please try again.');
    }
  } catch (error) {
    console.error('Error approving reservation:', error);

    // Show more detailed error message if available
    if (error.response && error.response.data && error.response.data.message) {
      toast.error(`Error: ${error.response.data.message}`);
    } else {
      toast.error('Could not approve reservation due to a technical issue. Please try refreshing the page.');
    }
  }
};
</script>

<template>
  <div class="min-h-screen bg-background text-foreground p-8">
    <div class="rounded-lg border border-border bg-card p-8 shadow-sm">
      <!-- Header with Navigation -->
      <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h1 class="text-3xl font-bold">{{ showAll ? 'All Reservations' : 'Pending Reservations' }}</h1>
          <p class="mt-2 text-muted-foreground">{{ showAll ? 'Showing all reservations in the system' : 'Showing reservations that need your approval' }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
          <Button
            v-if="!showAll"
            @click="router.visit(route('receptionist.all-reservations'), { preserveScroll: false, preserveState: false })"
            variant="default"
          >
            All Reservations
          </Button>
          <Button
            v-else
            @click="router.visit(route('receptionist.reservations'), { preserveScroll: false, preserveState: false })"
            variant="default"
          >
            Pending Reservations
          </Button>
          <Button
            @click="router.visit(route('receptionist.clients.approved'), { preserveScroll: false, preserveState: false })"
            variant="outline"
          >
            My Approved Clients
          </Button>
        </div>
      </div>

      <!-- No Reservations Message -->
      <div v-if="reservations.data.length === 0" class="text-center text-muted-foreground py-8 border rounded-lg">
        <p class="text-lg">
          {{ showAll ? 'No reservations found.' : 'No pending reservations found.' }}
        </p>
      </div>

      <!-- Reservations Table -->
      <div v-else class="rounded-lg border border-border overflow-hidden">
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
                @click.prevent="approveReservation(row)"
              >
                Approve
              </Button>
              <Button
                @click="viewReservation(row.original ? row.original.id : row.id)"
                variant="outline"
                size="sm"
              >
                View
              </Button>
              <Button
                @click="editReservation(row.original ? row.original.id : row.id)"
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