<script setup lang="ts">
import { ref, computed, defineProps, defineEmits } from 'vue'
import {
  Button,
} from '@/components/ui/button'

import {
  Pagination,
  PaginationEllipsis,
  PaginationFirst,
  PaginationLast,
  PaginationList,
  PaginationListItem,
  PaginationNext,
  PaginationPrev,
} from '@/components/ui/pagination'

defineProps({
  currentPage: {
    type: Number,
    required: true,
    default: 1
  },
  totalPages: {
    type: Number,
    required: true,
    default: 1
  }
})

const emit = defineEmits(['pageChange'])

const handlePageChange = (page: number) => {
  if (page >= 1 && page <= props.totalPages) {
    emit('pageChange', page)
  }
}
</script>

<template>
  <Pagination 
    :current-page="currentPage" 
    :total-pages="totalPages" 
    :items-per-page="10" 
    :sibling-count="1" 
    show-edges
  >
    <PaginationList class="flex items-center gap-1">
      <PaginationFirst @click="handlePageChange(1)" />
      <PaginationPrev 
        @click="handlePageChange(currentPage - 1)" 
        :disabled="currentPage === 1" 
      />

      <template v-for="page in totalPages" :key="page">
        <PaginationListItem as-child>
          <Button 
            class="w-9 h-9 p-0" 
            :variant="page === currentPage ? 'default' : 'outline'" 
            @click="handlePageChange(page)"
          >
            {{ page }}
          </Button>
        </PaginationListItem>
      </template>

      <PaginationNext 
        @click="handlePageChange(currentPage + 1)" 
        :disabled="currentPage === totalPages" 
      />
      <PaginationLast @click="handlePageChange(totalPages)" />
    </PaginationList>
  </Pagination>
</template>