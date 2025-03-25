<template>
  <div class="w-full data-table">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th v-for="column in columns" :key="column.accessorKey || column.id"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            {{ column.header }}
          </th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <tr v-for="row in processedData" :key="row.id" class="hover:bg-gray-50">
          <td v-for="column in columns" :key="column.accessorKey || column.id"
              class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            <slot :name="`cell-${column.accessorKey || column.id}`" :row="row">
              {{ getValue(row, column) }}
            </slot>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination -->
    <div v-if="pagination" class="px-4 py-3 flex items-center justify-between border-t border-gray-200">
      <div class="flex-1 flex justify-between items-center">
        <div>
          <p class="text-sm text-gray-700">
            Showing {{ pagination.pageSize * pagination.pageIndex + 1 }} to
            {{ Math.min(pagination.pageSize * (pagination.pageIndex + 1), pagination.totalItems) }} of
            {{ pagination.totalItems }} results
          </p>
        </div>
        <div>
          <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
            <button
              @click="handlePageChange(pagination.pageIndex - 1)"
              :disabled="pagination.pageIndex === 0"
              class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
            >
              Previous
            </button>
            <button
              @click="handlePageChange(pagination.pageIndex + 1)"
              :disabled="(pagination.pageIndex + 1) * pagination.pageSize >= pagination.totalItems"
              class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
            >
              Next
            </button>
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits, computed } from 'vue';

const props = defineProps({
  columns: {
    type: Array,
    required: true
  },
  data: {
    type: Array,
    required: true
  },
  pagination: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['pageChange']);

// Process data to ensure each row has an 'original' property
const processedData = computed(() => {
  return props.data.map(row => {
    // If the row already has an 'original' property, return it as is
    if (row.original) return row;

    // Otherwise, create a new object with the original data
    return {
      ...row,
      original: row
    };
  });
});

const getValue = (row, column) => {
  if (column.cell) {
    return column.cell({ row });
  }

  if (column.accessorKey) {
    return column.accessorKey.split('.').reduce((obj, key) => obj?.[key], row);
  }

  return '';
};

const handlePageChange = (newPage) => {
  emit('pageChange', newPage);
};
</script>