<template>
  <div class="min-h-screen bg-background text-foreground p-8">
    <div class="mx-auto max-w-7xl">
      <!-- Header with Navigation -->
      <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h1 class="text-3xl font-bold">All Clients</h1>
          <p class="mt-2 text-muted-foreground">System-wide view of all clients</p>
        </div>
        <div class="flex flex-wrap gap-3">
          <Button
            @click="navigateTo('/receptionist/clients')"
            variant="default"
          >
            Manage Clients
          </Button>
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
            All My Clients Reservations
          </Button>
          <Button
            @click="navigateTo('/receptionist/reservations')"
            variant="outline"
          >
            Pending Reservations
          </Button>
        </div>
      </div>

      <!-- All Clients Section -->
      <div>
        <div v-if="allClients.data.length === 0" class="text-center py-8 border rounded-lg">
          <p class="text-lg text-muted-foreground">No clients found in the system.</p>
        </div>

        <div v-else class="overflow-hidden rounded-lg border border-border">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead
                  @click="sortAndPaginate(1, 10, 'name', currentSort.field === 'name' && currentSort.direction === 'asc' ? 'desc' : 'asc')"
                  class="cursor-pointer hover:bg-accent/10"
                >
                  Client Name
                  <span v-if="currentSort.field === 'name'" class="ml-1">
                    {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                  </span>
                </TableHead>
                <TableHead
                  @click="sortAndPaginate(1, 10, 'email', currentSort.field === 'email' && currentSort.direction === 'asc' ? 'desc' : 'asc')"
                  class="cursor-pointer hover:bg-accent/10"
                >
                  Email
                  <span v-if="currentSort.field === 'email'" class="ml-1">
                    {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                  </span>
                </TableHead>
                <TableHead
                  @click="sortAndPaginate(1, 10, 'mobile', currentSort.field === 'mobile' && currentSort.direction === 'asc' ? 'desc' : 'asc')"
                  class="cursor-pointer hover:bg-accent/10"
                >
                  Mobile
                  <span v-if="currentSort.field === 'mobile'" class="ml-1">
                    {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                  </span>
                </TableHead>
                <TableHead
                  @click="sortAndPaginate(1, 10, 'country', currentSort.field === 'country' && currentSort.direction === 'asc' ? 'desc' : 'asc')"
                  class="cursor-pointer hover:bg-accent/10"
                >
                  Country
                  <span v-if="currentSort.field === 'country'" class="ml-1">
                    {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                  </span>
                </TableHead>
                <TableHead
                  @click="sortAndPaginate(1, 10, 'is_approved', currentSort.field === 'is_approved' && currentSort.direction === 'asc' ? 'desc' : 'asc')"
                  class="cursor-pointer hover:bg-accent/10"
                >
                  Status
                  <span v-if="currentSort.field === 'is_approved'" class="ml-1">
                    {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                  </span>
                </TableHead>
                <TableHead>Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="client in allClients.data" :key="client.id" class="hover:bg-accent/10">
                <TableCell class="font-medium">{{ client.name }}</TableCell>
                <TableCell>{{ client.email }}</TableCell>
                <TableCell>{{ client.mobile || 'N/A' }}</TableCell>
                <TableCell>{{ client.country || 'N/A' }}</TableCell>
                <TableCell>
                  <Badge :variant="client.is_approved ? 'success' : 'warning'">
                    {{ getStatusText(client) }}
                  </Badge>
                </TableCell>
                <TableCell>
                  <div class="flex space-x-2">
                    <!-- View Reservations button for all roles -->
                    <Button
                      @click="navigateTo(`/receptionist/clients/${client.id}/reservations`)"
                      variant="outline"
                      size="sm"
                    >
                      View Reservations
                    </Button>

                    <!-- Edit/Delete Actions (Admin Only) -->
                    <Button
                      v-if="isAdmin"
                      @click="editClient(client.id)"
                      variant="default"
                      size="sm"
                    >
                      Edit
                    </Button>
                    <Button
                      v-if="isAdmin"
                      @click="deleteClient(client.id)"
                      variant="destructive"
                      size="sm"
                    >
                      Delete
                    </Button>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </div>

      <!-- Enhanced Pagination -->
      <div v-if="allClients.data.length > 0" class="mt-4 flex justify-between items-center">
        <div class="text-sm text-muted-foreground">
          Showing {{ allClients.from }} to {{ allClients.to }} of {{ allClients.total }} clients
        </div>

        <!-- Page Size Selector -->
        <div class="flex items-center space-x-4">
          <div class="flex items-center">
            <span class="text-sm text-muted-foreground mr-2">Per page:</span>
            <select
              v-model="perPage"
              @change="sortAndPaginate(1, perPage, currentSort.field, currentSort.direction)"
              class="h-9 w-20 rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
            >
              <option value="5">5</option>
              <option value="10">10</option>
              <option value="25">25</option>
              <option value="50">50</option>
            </select>
          </div>

          <!-- Page Navigation -->
          <div class="flex space-x-2">
            <Button
              v-for="page in allClients.links"
              :key="page.label"
              @click="page.url && sortAndPaginate(
                page.label === '&laquo; Previous' ? allClients.current_page - 1 :
                page.label === 'Next &raquo;' ? allClients.current_page + 1 :
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

      <!-- Client Statistics Dashboard -->
      <div class="mt-8 p-6 bg-card rounded-lg border border-border shadow-sm">
        <h3 class="text-xl font-semibold mb-4">Client Statistics</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          <div class="p-4 rounded-lg bg-accent/10">
            <div class="text-sm text-muted-foreground">Total Clients</div>
            <div class="text-2xl font-bold">{{ clientStats.totalClients }}</div>
          </div>
          <div class="p-4 rounded-lg bg-success/10">
            <div class="text-sm text-muted-foreground">Approved Clients</div>
            <div class="text-2xl font-bold">{{ clientStats.approvedClients }}</div>
          </div>
          <div class="p-4 rounded-lg bg-warning/10">
            <div class="text-sm text-muted-foreground">Pending Clients</div>
            <div class="text-2xl font-bold">{{ clientStats.pendingClients }}</div>
          </div>
        </div>

        <h4 class="text-lg font-semibold mb-3">Recent Reservations</h4>
        <div v-if="recentReservations.length === 0" class="text-center py-4 bg-accent/10 rounded-lg">
          <p class="text-muted-foreground">No recent reservations found.</p>
        </div>
        <div v-else class="overflow-hidden rounded-lg border border-border">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Client</TableHead>
                <TableHead>Room</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Check-in</TableHead>
                <TableHead>Check-out</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="reservation in recentReservations" :key="reservation.id" class="hover:bg-accent/10">
                <TableCell class="font-medium">
                  {{ reservation.client ? reservation.client.name : 'N/A' }}
                </TableCell>
                <TableCell>
                  {{ reservation.room ? reservation.room.room_number : 'N/A' }}
                </TableCell>
                <TableCell>
                  <Badge :variant="getReservationStatusVariant(reservation.status)">
                    {{ reservation.status }}
                  </Badge>
                </TableCell>
                <TableCell>{{ formatDate(reservation.check_in_date) }}</TableCell>
                <TableCell>{{ formatDate(reservation.check_out_date) }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </div>

      <!-- Confirmation Dialog -->
      <Dialog v-if="showConfirmDialog" open>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>{{ confirmDialogTitle }}</DialogTitle>
            <DialogDescription>{{ confirmDialogMessage }}</DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button variant="outline" @click="cancelConfirmation">Cancel</Button>
            <Button variant="destructive" @click="confirmDelete()">Delete</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';

defineOptions({ layout: AppLayout });

// Props
const props = defineProps({
  allClients: {
    type: Object,
    required: true
  },
  clientStats: {
    type: Object,
    default: () => ({
      totalClients: 0,
      approvedClients: 0,
      pendingClients: 0
    })
  },
  recentReservations: {
    type: Array,
    default: () => []
  },
  userRole: {
    type: String,
    default: 'receptionist'
  },
  isAdmin: {
    type: Boolean,
    default: false
  },
  currentUserId: {
    type: Number,
    required: true
  },
  debug: {
    type: Object,
    default: null
  }
});

// State
const showConfirmDialog = ref(false);
const confirmDialogTitle = ref('');
const confirmDialogMessage = ref('');
const selectedClientId = ref(null);
const currentSort = ref({
  field: 'created_at',
  direction: 'desc'
});
const perPage = ref(10);

// Methods
const goToPage = (url) => {
  if (!url) return;

  // Extract the query parameters from the URL
  const urlObj = new URL(url, window.location.origin);
  const params = Object.fromEntries(urlObj.searchParams.entries());

  // Use Inertia's get method with the extracted parameters
  router.get(urlObj.pathname, params, {
    preserveScroll: true,
    preserveState: true,
    only: ['allClients', 'clientStats', 'recentReservations']
  });
};

// Navigation method using Inertia
const navigateTo = (url) => {
  router.get(url, {}, {
    preserveScroll: false,
    preserveState: false,
    replace: false
  });
};

// Enhanced pagination with sorting
const sortAndPaginate = (page = 1, perPage = 10, sortBy = 'created_at', sortDir = 'desc') => {
  // Update current sort state
  currentSort.value = {
    field: sortBy,
    direction: sortDir
  };

  // Navigate with new parameters using Inertia's get method
  router.get(window.location.pathname, {
    page,
    per_page: perPage,
    sort_by: sortBy,
    sort_dir: sortDir
  }, {
    preserveScroll: true,
    preserveState: true, // Keep component state between requests
    only: ['allClients', 'clientStats', 'recentReservations'], // Only refresh these data props
    replace: true // Replace current history entry instead of adding a new one
  });
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  try {
    const date = new Date(dateString);
    return date.toLocaleDateString();
  } catch (e) {
    return dateString;
  }
};

// Updated to return variant names for Badge component
const getReservationStatusVariant = (status) => {
  const variants = {
    confirmed: 'success',
    checked_in: 'info',
    'checked-in': 'info',
    checked_out: 'secondary',
    'checked-out': 'secondary',
    pending: 'warning',
    cancelled: 'destructive',
  };
  return variants[status] || 'secondary';
};

const getStatusText = (client) => {
  return client.is_approved ? 'Approved' : 'Pending';
};

const cancelConfirmation = () => {
  showConfirmDialog.value = false;
  selectedClientId.value = null;
};

// Edit client method
const editClient = (clientId) => {
  // Navigate to the edit page using Inertia
  router.get(`/receptionist/clients/${clientId}/edit`, {}, {
    preserveState: false // Don't preserve state when navigating to a different page
  });
};

// Delete client method
const deleteClient = (clientId) => {
  selectedClientId.value = clientId;
  confirmDialogTitle.value = 'Confirm Client Deletion';
  confirmDialogMessage.value = 'Are you sure you want to delete this client? This action cannot be undone and will remove all associated reservations.';
  showConfirmDialog.value = true;
};

// Confirm delete method
const confirmDelete = () => {
  if (!selectedClientId.value) return;

  // Hide dialog first
  showConfirmDialog.value = false;

  // Use Inertia's delete method
  router.delete(`/receptionist/clients/${selectedClientId.value}`, {}, {
    onSuccess: () => {
      // Success is handled automatically by Inertia refreshing the page data
      selectedClientId.value = null;
    },
    onError: (errors) => {
      console.error('Error deleting client:', errors);
      alert('Could not delete client due to a technical issue. Please try again.');
    },
    preserveScroll: true
  });
};
</script>


