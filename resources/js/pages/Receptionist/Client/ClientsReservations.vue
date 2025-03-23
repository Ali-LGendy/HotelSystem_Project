<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-gray-900 overflow-hidden shadow-xl sm:rounded-lg">
      <div class="p-6 sm:px-8 bg-gray-900 border-b border-gray-700">
        <!-- Header with Navigation -->
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-bold text-white">
            {{ clientId ? 'Client Reservations' : 'All Approved Clients Reservations' }}
            <span v-if="clientId && clientsReservations.data.length > 0" class="text-lg ml-2 text-gray-300">
              ({{ clientsReservations.data[0].client.name }})
            </span>
          </h1>
          <div class="flex space-x-3">
            <Link
              :href="route('receptionist.clients.pending')"
              class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 transition"
            >
              Pending Clients
            </Link>
            <Link
              :href="route('receptionist.clients.approved')"
              class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition"
            >
              My Approved Clients
            </Link>
            <Link
              :href="route('receptionist.reservations.index')"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
            >
              Back to Reservations
            </Link>
          </div>
        </div>

        <div v-if="clientsReservations.data.length === 0" class="text-center py-8">
          <p class="text-lg text-gray-300">No reservations found for your approved clients.</p>
        </div>

        <!-- Data Table -->
        <DataTable
          v-else
          :columns="columns"
          :data="clientsReservations.data"
          :pagination="{
            pageSize: 10,
            pageIndex: currentPage - 1,
            totalItems: clientsReservations.total,
            manualPagination: true
          }"
          @page-change="handlePageChange"
          class="text-gray-200"
        >
          <!-- Status Cell Template -->
          <template #cell-status="{ row }">
            <Badge :variant="getStatusVariant(row.status)">
              {{ row.status }}
            </Badge>
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
import { Badge } from '@/components/ui/badge';

// Props
const props = defineProps({
  clientsReservations: {
    type: Object,
    required: true
  },
  clientId: {
    type: [Number, String],
    default: null
  }
});

// Table Columns
const columns = [
  {
    accessorKey: 'client.name',
    header: 'Client Name'
  },
  {
    accessorKey: 'accompany_number',
    header: 'Accompany Number'
  },
  {
    accessorKey: 'room.room_number',
    header: 'Room Number'
  },
  {
    accessorKey: 'price_paid',
    header: 'Paid Price',
    cell: ({ row }) => row.original && row.original.price_paid ? `$${row.original.price_paid}` : 'N/A'
  },
  {
    accessorKey: 'room.price',
    header: 'Current Room Price',
    cell: ({ row }) => row.original && row.original.room && row.original.room.price ? `$${row.original.room.price}` : 'N/A'
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
  }
];

// Computed
const currentPage = computed(() => {
  return props.clientsReservations.current_page || 1;
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
  if (props.clientId) {
    router.get(
      route('receptionist.clients.client-reservations', {
        id: props.clientId,
        page: pageIndex + 1
      }),
      {},
      { preserveState: true, preserveScroll: true }
    );
  } else {
    router.get(
      route('receptionist.clients.reservations', { page: pageIndex + 1 }),
      {},
      { preserveState: true, preserveScroll: true }
    );
  }
};
</script>