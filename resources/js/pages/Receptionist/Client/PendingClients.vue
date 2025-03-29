<template>
  <div class="min-h-screen bg-background text-foreground p-8">
    <div class="mx-auto max-w-7xl">
      <!-- Header with Navigation -->
      <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h1 class="text-3xl font-bold">Clients Pending Approval</h1>
          <p class="mt-2 text-muted-foreground">Manage client registration requests</p>
        </div>
        <div class="flex flex-wrap gap-3">
          <Button
            @click="navigateTo('/receptionist/clients/my-approved')"
            variant="default"
          >
            My Approved Clients
          </Button>
          <Button
            @click="navigateTo('/receptionist/clients/reservations')"
            variant="outline"
          >
            Clients Reservations
          </Button>
        </div>
      </div>

      <div v-if="pendingClients.data.length === 0" class="text-center py-8 border rounded-lg">
        <p class="text-lg text-muted-foreground">No pending client registrations found.</p>
      </div>

      <!-- Data Table -->
      <div v-else class="overflow-hidden rounded-lg border border-border">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Client Name</TableHead>
              <TableHead>Email</TableHead>
              <TableHead>Mobile</TableHead>
              <TableHead>Country</TableHead>
              <TableHead>Gender</TableHead>
              <TableHead>Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="client in pendingClients.data" :key="client.id" class="hover:bg-accent/10">
              <TableCell class="font-medium">{{ client.name }}</TableCell>
              <TableCell>{{ client.email }}</TableCell>
              <TableCell>{{ client.mobile || 'N/A' }}</TableCell>
              <TableCell>{{ client.country || 'N/A' }}</TableCell>
              <TableCell>{{ client.gender || 'N/A' }}</TableCell>
              <TableCell>
                <div class="flex space-x-2">
                  <Button
                    @click="approveClient(client.id)"
                    variant="success"
                    size="sm"
                  >
                    Approve
                  </Button>
                  <Button
                    @click="rejectClient(client.id)"
                    variant="destructive"
                    size="sm"
                  >
                    Reject
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <!-- Pagination -->
      <div v-if="pendingClients.data.length > 0" class="mt-4 flex justify-between items-center">
        <div class="text-sm text-muted-foreground">
          Showing {{ pendingClients.from }} to {{ pendingClients.to }} of {{ pendingClients.total }} clients
        </div>
        <div class="flex space-x-2">
          <Button
            v-for="page in pendingClients.links"
            :key="page.label"
            @click="goToPage(page.url)"
            :disabled="!page.url"
            :variant="page.active ? 'default' : 'outline'"
            size="sm"
            v-html="page.label"
          ></Button>
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
            <Button
              :variant="confirmAction === 'approve' ? 'default' : 'destructive'"
              @click="confirmAction === 'approve' ? confirmApprove() : confirmReject()"
            >
              {{ confirmAction === 'approve' ? 'Approve' : 'Reject' }}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { route } from 'ziggy-js';
import { useToast } from '@/composables/useToast';
import AppLayout from '@/layouts/AppLayout.vue';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';

defineOptions({ layout: AppLayout });

// Initialize toast
const toast = useToast();

// Props
const props = defineProps({
  pendingClients: {
    type: Object,
    required: true
  }
});

// Navigation method using Inertia
const navigateTo = (url) => {
  router.get(url, {}, {
    preserveScroll: false,
    preserveState: false,
    replace: false
  });
};

// Pagination
const goToPage = (url) => {
  if (url) {
    router.get(url, {}, {
      preserveScroll: true,
      preserveState: true,
    });
  }
};

// Client approval and rejection
const showConfirmDialog = ref(false);
const confirmDialogTitle = ref('');
const confirmDialogMessage = ref('');
const confirmAction = ref('');
const selectedClientId = ref(null);

// Direct approval without confirmation dialog
const approveClient = (clientId) => {
  // Find the client name for the toast message
  const client = props.pendingClients.data.find(c => c.id === clientId);
  const clientName = client ? client.name : 'Client';
  
  router.post(route('receptionist.clients.approve', clientId), {}, {
    preserveScroll: true,
    onSuccess: () => {
      // Use a simpler toast call that's more likely to work
      toast.success(`${clientName} has been approved successfully!`);
    },
    onError: (errors) => {
      toast.error(`Failed to approve client: ${Object.values(errors).flat().join(', ')}`);
    }
  });
};

// Direct rejection without confirmation dialog
const rejectClient = (clientId) => {
  // Find the client name for the toast message
  const client = props.pendingClients.data.find(c => c.id === clientId);
  const clientName = client ? client.name : 'Client';
  
  // Show confirmation dialog before rejecting
  showConfirmDialog.value = true;
  confirmDialogTitle.value = 'Confirm Rejection';
  confirmDialogMessage.value = `Are you sure you want to reject ${clientName}'s registration? This action cannot be undone.`;
  confirmAction.value = 'reject';
  selectedClientId.value = clientId;
};

// These functions are referenced in the template but not used in the current implementation
// Adding them to prevent errors
const cancelConfirmation = () => {
  showConfirmDialog.value = false;
};

const confirmApprove = () => {
  // Implementation would go here if needed
  showConfirmDialog.value = false;
};

const confirmReject = () => {
  const clientId = selectedClientId.value;
  if (!clientId) return;
  
  const client = props.pendingClients.data.find(c => c.id === clientId);
  const clientName = client ? client.name : 'Client';
  
  router.post(route('receptionist.clients.reject', clientId), {}, {
    preserveScroll: true,
    onSuccess: () => {
      toast.success(`${clientName}'s registration has been rejected.`);
      showConfirmDialog.value = false;
    },
    onError: (errors) => {
      toast.error(`Failed to reject client: ${Object.values(errors).flat().join(', ')}`);
      showConfirmDialog.value = false;
    }
  });
};
</script>
