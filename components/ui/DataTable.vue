<template>
  <div>
    <table class="min-w-full divide-y divide-gray-200">
      <thead>
        <tr>
          <th v-for="column in columns" :key="column.accessorKey" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            {{ column.header }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="row in data" :key="row.id" class="bg-white divide-y divide-gray-200">
          <td v-for="column in columns" :key="column.accessorKey" class="px-6 py-4 whitespace-nowrap">
            <slot :name="`cell-${column.accessorKey}`" :row="row">{{ row[column.accessorKey] }}</slot>
          </td>
        </tr>
      </tbody>
    </table>
    <div class="mt-4">
      <Pagination :current-page="pagination.pageIndex + 1" :total-items="pagination.totalItems" :page-size="pagination.pageSize" @page-change="$emit('page-change', $event)" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  columns: Array,
  data: Array,
  pagination: Object
});
</script>

<style scoped>
/* Add any necessary styles here */
</style>
