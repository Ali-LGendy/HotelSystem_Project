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
          <a
            href="/receptionist/clients/my-approved"
            class="rounded-lg bg-green-600 px-4 py-2 font-semibold text-white transition hover:bg-green-700"
          >
            My Approved Clients
          </a>
          <a
            href="/receptionist/clients/reservations"
            class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white transition hover:bg-indigo-700"
          >
            Clients Reservations
          </a>
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
                  <button
                    @click="approveClient(client.id)"
                    class="rounded-md bg-green-700 px-3 py-1 text-sm font-medium text-white hover:bg-green-600"
                  >
                    Approve
                  </button>
                  <button
                    @click="rejectClient(client.id)"
                    class="rounded-md bg-red-800 px-3 py-1 text-sm font-medium text-white hover:bg-red-700"
                  >
                    Reject
                  </button>
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
          <button
            v-for="page in pendingClients.links"
            :key="page.label"
            @click="goToPage(page.url)"
            :disabled="!page.url"
            :class="[
              'px-3 py-1 rounded-md text-sm',
              page.active 
                ? 'bg-blue-600 text-white' 
                : page.url 
                  ? 'bg-gray-700 text-gray-200 hover:bg-gray-600' 
                  : 'bg-gray-800 text-gray-500 cursor-not-allowed'
            ]"
            v-html="page.label"
          ></button>
        </div>
      </div>

      <!-- Confirmation Dialog -->
      <div v-if="showConfirmDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="w-full max-w-md rounded-lg bg-gray-800 p-6 text-gray-200 shadow-xl">
          <h3 class="text-xl font-semibold text-gray-100">{{ confirmDialogTitle }}</h3>
          <p class="mt-2 text-gray-400">{{ confirmDialogMessage }}</p>
          <div class="mt-6 flex justify-end space-x-3">
            <button
              class="rounded-md border border-gray-600 bg-gray-700 px-4 py-2 text-sm font-medium text-gray-200 hover:bg-gray-600"
              @click="cancelConfirmation"
            >
              Cancel
            </button>
            <button
              :class="[
                'rounded-md px-4 py-2 text-sm font-medium text-white',
                confirmAction === 'approve' 
                  ? 'bg-green-700 hover:bg-green-600' 
                  : 'bg-red-700 hover:bg-red-600'
              ]"
              @click="confirmAction === 'approve' ? confirmApprove() : confirmReject()"
            >
              {{ confirmAction === 'approve' ? 'Approve' : 'Reject' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

// Props
const props = defineProps({
  pendingClients: {
    type: Object,
    required: true
  }
});

// State
const showConfirmDialog = ref(false);
const confirmDialogTitle = ref('');
const confirmDialogMessage = ref('');
const confirmAction = ref('');
const selectedClientId = ref(null);

// Methods
const approveClient = (clientId) => {
  selectedClientId.value = clientId;
  confirmAction.value = 'approve';
  confirmDialogTitle.value = 'Confirm Client Approval';
  confirmDialogMessage.value = 'Are you sure you want to approve this client? They will be added to your approved clients list.';
  showConfirmDialog.value = true;
};

const rejectClient = (clientId) => {
  selectedClientId.value = clientId;
  confirmAction.value = 'reject';
  confirmDialogTitle.value = 'Confirm Client Rejection';
  confirmDialogMessage.value = 'Are you sure you want to reject this client? This action cannot be undone.';
  showConfirmDialog.value = true;
};

const cancelConfirmation = () => {
  showConfirmDialog.value = false;
  selectedClientId.value = null;
};

const confirmApprove = () => {
  if (!selectedClientId.value) return;
  
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = `/receptionist/clients/${selectedClientId.value}/approve`;
  
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const csrfInput = document.createElement('input');
  csrfInput.type = 'hidden';
  csrfInput.name = '_token';
  csrfInput.value = csrfToken;
  
  form.appendChild(csrfInput);
  document.body.appendChild(form);
  form.submit();
};

const confirmReject = () => {
  if (!selectedClientId.value) return;
  
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = `/receptionist/clients/${selectedClientId.value}/reject`;
  
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const csrfInput = document.createElement('input');
  csrfInput.type = 'hidden';
  csrfInput.name = '_token';
  csrfInput.value = csrfToken;
  
  form.appendChild(csrfInput);
  document.body.appendChild(form);
  form.submit();
};

const goToPage = (url) => {
  if (!url) return;
  window.location.href = url;
};
</script>

<style scoped>
.pagination-link {
  @apply px-3 py-1 rounded-md text-sm;
}

.pagination-link-active {
  @apply bg-blue-600 text-white;
}

.pagination-link-inactive {
  @apply bg-gray-700 text-gray-200 hover:bg-gray-600;
}

.pagination-link-disabled {
  @apply bg-gray-800 text-gray-500 cursor-not-allowed;
}
</style><template>
  <div class="mx-auto max-w-7xl px-4 py-8">
    <div class="rounded-lg bg-gray-900 p-8 text-gray-200 shadow-lg">
      <!-- Header with Navigation -->
      <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <h2 class="text-3xl font-bold">Clients Pending Approval</h2>
        <div class="flex flex-wrap gap-3">
          <button
            @click="router.visit(route('receptionist.clients.my-approved'))"
            class="rounded-lg bg-green-600 px-4 py-2 font-semibold text-white transition hover:bg-green-700"
          >
            My Approved Clients
          </button>
          <button
            @click="router.visit(route('receptionist.clients.reservations'))"
            class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white transition hover:bg-indigo-700"
          >
            Clients Reservations
          </button>
          <button
            @click="goBack"
            class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white transition hover:bg-blue-700"
          >
            Back to Reservations
          </button>
        </div>
      </div>

      <div v-if="pendingClients.data.length === 0" class="text-center py-8">
        <p class="text-lg text-gray-300">No clients pending approval found.</p>
      </div>

      <!-- Data Table -->
      <div v-else class="rounded-lg border border-gray-700 bg-gray-800 overflow-hidden">
        <DataTable
          :columns="columns"
          :data="pendingClients.data"
          :pagination="{
            pageSize: 10,
            pageIndex: currentPage - 1,
            totalItems: pendingClients.total,
            manualPagination: true
          }"
          @page-change="handlePageChange"
          class="text-gray-200"
        >
          <!-- Actions Cell Template -->
          <template #cell-actions="{ row }">
            <div class="flex items-center gap-2">
              <Button
                variant="success"
                size="sm"
                @click="approveClient(row)"
                class="bg-green-600 hover:bg-green-700 text-white"
              >
                Approve Client
              </Button>
              <Button
                variant="destructive"
                size="sm"
                @click="confirmRejectClient(row)"
                class="bg-red-600 hover:bg-red-700 text-white"
              >
                Reject Client
              </Button>
            </div>
          </template>
        </DataTable>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import DataTable from '@/components/ui/DataTable.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';

// Props
const props = defineProps({
  pendingClients: {
    type: Object,
    required: true
   
  }
});

// Table Columns
const columns = [
  {
    accessorKey: 'name',
    header: 'Client Name',
    cell: ({ row }) => row.original ? row.original.name : 'N/A'
  },
  {
    accessorKey: 'email',
    header: 'Email',
    cell: ({ row }) => row.original ? row.original.email : 'N/A'
  },
  {
    accessorKey: 'mobile',
    header: 'Mobile',
    cell: ({ row }) => row.original && row.original.mobile ? row.original.mobile : 'N/A'
  },
  {
    accessorKey: 'country',
    header: 'Country',
    cell: ({ row }) => row.original ? row.original.country : 'N/A'
  },
  {
    accessorKey: 'gender',
    header: 'Gender',
    cell: ({ row }) => row.original ? row.original.gender : 'N/A'
  },
  {
    id: 'actions',
    header: 'Actions'
  }
];

// Computed
const currentPage = computed(() => {
  return props.pendingClients.current_page || 1;
});

// Methods
const handlePageChange = (pageIndex) => {
  router.visit(
    route('receptionist.clients.index', { page: pageIndex + 1 }),
    {
      only: ['pendingClients'],
      preserveState: true,
      preserveScroll: true
    }
  );
};

const goBack = () => {
  router.visit(route('receptionist.reservations.index'), {
    preserveScroll: true
  });
};

const approveClient = (client) => {
  const id = client.original ? client.original.id : client.id;
  console.log('Approving client with ID:', id);

  router.post(
    route('receptionist.clients.approve', id),
    {},
    {
      onSuccess: () => {
        console.log('Client approved successfully');
        // Redirect to the approved clients page to show the newly approved client
        router.visit(route('receptionist.clients.index'), {
          preserveScroll: true
        });
      },
      onError: (errors) => {
        console.error('Error approving client:', errors);
        alert('Error approving client. Please try again.');
      }
    }
  );
};

const confirmRejectClient = (client) => {
  const id = client.original ? client.original.id : client.id;
  const name = client.original ? client.original.name : client.name;

  if (confirm(`Are you sure you want to reject client "${name}"? This action cannot be undone.`)) {
    rejectClient(id);
  }
};

const rejectClient = (id) => {
  console.log('Rejecting client with ID:', id);

  router.post(
    route('receptionist.clients.reject', id),
    {},
    {
      onSuccess: () => {
        console.log('Client rejected successfully');
        // Refresh the page to show updated data
        router.visit(route('receptionist.clients.index'), {
          only: ['pendingClients'],
          preserveScroll: true
        });
      },
      onError: (errors) => {
        console.error('Error rejecting client:', errors);
        alert('Error rejecting client. Please try again.');
      }
    }
  );
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