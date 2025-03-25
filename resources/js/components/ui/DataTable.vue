<template>
  <div class="w-full data-table">
    <table class="min-w-full divide-y divide-gray-700">
      <thead class="bg-gray-800">
        <tr>
          <th v-for="column in columns" :key="column.accessorKey || column.id"
              class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
            {{ column.header }}
          </th>
        </tr>
      </thead>
      <tbody class="bg-gray-800 divide-y divide-gray-700">
        <tr v-for="row in processedData" :key="row.id" class="hover:bg-gray-700">
          <td v-for="column in columns" :key="column.accessorKey || column.id"
              class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
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
              :class="pagination.pageIndex === 0 ? 'opacity-50 cursor-not-allowed' : ''"
            >
              Previous
            </button>

            <!-- Page Numbers -->
            <button
              v-for="page in getPageNumbers()"
              :key="page"
              @click="handlePageChange(page - 1)"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium hover:bg-gray-50"
              :class="page - 1 === pagination.pageIndex ? 'bg-blue-50 text-blue-600 z-10' : 'text-gray-500'"
            >
              {{ page }}
            </button>

            <button
              @click="handlePageChange(pagination.pageIndex + 1)"
              :disabled="(pagination.pageIndex + 1) * pagination.pageSize >= pagination.totalItems"
              class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
              :class="(pagination.pageIndex + 1) * pagination.pageSize >= pagination.totalItems ? 'opacity-50 cursor-not-allowed' : ''"
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

const getPageNumbers = () => {
  if (!props.pagination) return [];

  const totalPages = Math.ceil(props.pagination.totalItems / props.pagination.pageSize);
  const currentPage = props.pagination.pageIndex + 1;

  // Show at most 5 page numbers
  let startPage = Math.max(1, currentPage - 2);
  let endPage = Math.min(totalPages, startPage + 4);

  // Adjust if we're near the end
  if (endPage - startPage < 4) {
    startPage = Math.max(1, endPage - 4);
  }

  const pages = [];
  for (let i = startPage; i <= endPage; i++) {
    pages.push(i);
  }

  return pages;
};
</script>