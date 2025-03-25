<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-gray-900 overflow-hidden shadow-xl sm:rounded-lg">
      <div class="p-6 sm:px-8 bg-gray-900 border-b border-gray-700">
        <!-- Header with Navigation -->
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-bold text-white">Pending Clients</h1>
          <div class="flex space-x-3">
            <Link
              :href="route('receptionist.clients.approved')"
              class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition"
            >
              My Approved Clients
            </Link>
            <Link
              :href="route('receptionist.clients.reservations')"
              class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition"
            >
              Clients Reservations
            </Link>
            <Link
              :href="route('receptionist.reservations.index')"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
            >
              Back to Reservations
            </Link>
          </div>
        </div>

        <div v-if="pendingClients.data.length === 0" class="text-center py-8">
          <p class="text-lg text-gray-300">No pending clients found. All clients have been approved.</p>
        </div>

        <!-- Data Table -->
        <DataTable
          v-else
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
              <Button variant="success" size="sm" @click="approveClient(row)">
                Approve
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
import { Link, router } from '@inertiajs/vue3';
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
    header: 'Client Name'
  },
  {
    accessorKey: 'email',
    header: 'Email'
  },
  {
    accessorKey: 'mobile',
    header: 'Mobile',
    cell: ({ row }) => row.original && row.original.mobile ? row.original.mobile : 'N/A'
  },
  {
    accessorKey: 'country',
    header: 'Country'
  },
  {
    accessorKey: 'gender',
    header: 'Gender'
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
  router.get(
    route('receptionist.clients.pending', { page: pageIndex + 1 }),
    {},
    { preserveState: true, preserveScroll: true }
  );
};

const approveClient = (client) => {
  const id = client.original ? client.original.id : client.id;
  router.post(
    route('receptionist.clients.approve', id),
    {},
    {
      onSuccess: () => {
        // Refresh the page to show updated data
        router.reload({ only: ['pendingClients'] });
      }
    }
  );
};
</script>