<template>
  <div class="flex justify-between items-center py-4">
    <p class="text-sm text-gray-600">
      Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
    </p>
    <nav class="flex items-center space-x-2">
      <!-- Previous Page -->
      <button
        :disabled="pagination.current_page === 1"
        @click="changePage(pagination.current_page - 1)"
        class="px-4 py-2 rounded-md border border-gray-300 bg-white text-gray-500 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm transition"
      >
        <span class="material-icons text-lg">Previous</span>
      </button>

      <!-- Page Numbers -->
      <template v-if="pagination.last_page > 0">
        <!-- Ellipsis for large page ranges -->
        <template v-if="startPage > 1">
          <button
            @click="changePage(1)"
            class="px-3 py-2 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 shadow-sm"
          >
            1
          </button>
          <span v-if="startPage > 2" class="text-gray-500 px-2">...</span>
        </template>

        <!-- Visible Page Range -->
        <template v-for="i in pageRange" :key="i">
          <button
            :class="{
              'bg-blue-600 text-white': i === pagination.current_page,
              'bg-white text-gray-700': i !== pagination.current_page
            }"
            @click="changePage(i)"
            class="px-3 py-2 rounded-md border border-gray-300 shadow-sm hover:bg-gray-100 transition"
          >
            {{ i }}
          </button>
        </template>

        <!-- Ellipsis for remaining pages -->
        <template v-if="endPage < pagination.last_page">
          <span v-if="endPage < pagination.last_page - 1" class="text-gray-500 px-2">...</span>
          <button
            @click="changePage(pagination.last_page)"
            class="px-3 py-2 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 shadow-sm"
          >
            {{ pagination.last_page }}
          </button>
        </template>
      </template>

      <!-- Next Page -->
      <button
        :disabled="pagination.current_page === pagination.last_page"
        @click="changePage(pagination.current_page + 1)"
        class="px-4 py-2 rounded-md border border-gray-300 bg-white text-gray-500 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm transition"
      >
        <span class="material-icons text-lg">Next</span>
      </button>
    </nav>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  pagination: {
    type: Object,
    required: true
  }
});

// Calculate page ranges
const startPage = computed(() => Math.max(1, props.pagination.current_page - 2));
const endPage = computed(() => Math.min(props.pagination.last_page, props.pagination.current_page + 2));
const pageRange = computed(() => Array.from({ length: endPage.value - startPage.value + 1 }, (_, i) => i + startPage.value));

// Change page function
const changePage = (page) => {
  router.get(`${props.pagination.path}?page=${page}`);
};
</script>
