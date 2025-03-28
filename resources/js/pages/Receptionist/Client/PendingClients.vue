<template>
  <div class="mx-auto max-w-7xl px-4 py-8">
    <div class="rounded-lg bg-gray-900 p-8 text-gray-200 shadow-lg">
      <!-- Header with Navigation -->
      <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h2 class="text-3xl font-bold">Clients Pending Approval</h2>
          <p class="mt-2 text-gray-400">Manage client registration requests</p>
        </div>
        <div class="flex flex-wrap gap-3">
          <button
            @click="navigateTo('/receptionist/clients/my-approved')"
            class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
          >
            My Approved Clients
          </button>
          <button
            @click="navigateTo('/receptionist/clients/reservations')"
            class="inline-flex items-center justify-center rounded-md bg-secondary px-4 py-2 text-sm font-medium text-secondary-foreground shadow-sm transition-colors hover:bg-secondary/80 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
          >
            Clients Reservations
          </button>
        </div>
      </div>

      <div v-if="pendingClients.data.length === 0" class="text-center py-8">
        <p class="text-lg text-gray-300">No pending client registrations found.</p>
      </div>

      <!-- Data Table -->
      <div v-else class="rounded-lg border border-gray-700 bg-gray-800 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-700">
          <thead class="bg-gray-800">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                Client Name
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                Email
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                Mobile
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                Country
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                Gender
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-gray-800 divide-y divide-gray-700">
            <tr v-for="client in pendingClients.data" :key="client.id" class="hover:bg-gray-700">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-200">{{ client.name }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-300">{{ client.email }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-300">{{ client.mobile || 'N/A' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-300">{{ client.country || 'N/A' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-300">{{ client.gender || 'N/A' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
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
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pendingClients.data.length > 0" class="mt-4 flex justify-between items-center">
        <div class="text-sm text-gray-400">
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
      <AlertDialog v-if="showConfirmDialog">
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>{{ confirmDialogTitle }}</AlertDialogTitle>
            <AlertDialogDescription>
              {{ confirmDialogMessage }}
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel @click="cancelConfirmation">Cancel</AlertDialogCancel>
            <AlertDialogAction 
              @click="confirmAction === 'approve' ? confirmApprove() : confirmReject()"
              :variant="confirmAction === 'approve' ? 'default' : 'destructive'"
            >
              {{ confirmAction === 'approve' ? 'Approve' : 'Reject' }}
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { route } from 'ziggy-js';
import { useToast } from '@/composables/useToast';
import AppLayout from '@/layouts/AppLayout.vue';
defineOptions({ layout: AppLayout });
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';

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

  router.post(route('receptionist.clients.reject', clientId), {}, {
    preserveScroll: true,
    onSuccess: () => {
      toast.success(`${clientName} has been rejected.`);
    },
    onError: (errors) => {
      toast.error(`Failed to reject client: ${Object.values(errors).flat().join(', ')}`);
    }
  });
};
</script>
