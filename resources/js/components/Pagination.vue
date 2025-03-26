<template>
  <div class="rounded-b-lg border-t border-gray-200 px-4 py-2">
    <nav class="flex flex-wrap justify-between items-center">
      <p class="text-sm text-gray-700 w-full md:w-auto">
        Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
      </p>
      <div class="flex flex-wrap items-center space-x-2">
        <!-- Previous Page Link -->
        <button
          v-if="pagination.current_page === 1"
          class="px-3 py-2 rounded-md bg-gray-200 text-gray-500 cursor-not-allowed"
          disabled
        >
          Previous
        </button>
        <button
          v-else
          @click="changePage(pagination.current_page - 1)"
          class="px-3 py-2 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-100"
        >
          Previous
        </button>

        <!-- Page Numbers -->
        <template v-if="pagination.last_page > 0">
          <!-- First page and ellipsis -->
          <template v-if="startPage > 1">
            <button
              @click="changePage(1)"
              class="px-3 py-2 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-100"
            >
              1
            </button>
            <span v-if="startPage > 2" class="px-3 py-2 text-gray-500">...</span>
          </template>

          <!-- Page range -->
          <template v-for="i in pageRange" :key="i">
            <button
              v-if="i === pagination.current_page"
              class="px-3 py-2 rounded-md bg-blue-600 text-white"
              disabled
            >
              {{ i }}
            </button>
            <button
              v-else
              @click="changePage(i)"
              class="px-3 py-2 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-100"
            >
              {{ i }}
            </button>
          </template>

          <!-- Last page and ellipsis -->
          <template v-if="endPage < pagination.last_page">
            <span v-if="endPage < pagination.last_page - 1" class="px-3 py-2 text-gray-500">...</span>
            <button
              @click="changePage(pagination.last_page)"
              class="px-3 py-2 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-100"
            >
              {{ pagination.last_page }}
            </button>
          </template>
        </template>

        <!-- Next Page Link -->
        <button
          v-if="pagination.current_page === pagination.last_page"
          class="px-3 py-2 rounded-md bg-gray-200 text-gray-500 cursor-not-allowed"
          disabled
        >
          Next
        </button>
        <button
          v-else
          @click="changePage(pagination.current_page + 1)"
          class="px-3 py-2 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-100"
        >
          Next
        </button>
      </div>
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

// Calculate the start and end page numbers for pagination
const startPage = computed(() => {
  return Math.max(1, props.pagination.current_page - 2);
});

const endPage = computed(() => {
  return Math.min(props.pagination.last_page, props.pagination.current_page + 2);
});

// Generate an array of page numbers to display
const pageRange = computed(() => {
  const range = [];
  for (let i = startPage.value; i <= endPage.value; i++) {
    range.push(i);
  }
  return range;
});

// Function to change the page
const changePage = (page) => {
  router.get(props.pagination.path + '?page=' + page);
};
</script>