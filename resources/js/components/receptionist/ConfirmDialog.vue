<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="w-full max-w-md rounded-lg bg-gray-800 p-6 text-gray-200 shadow-xl">
      <h3 class="text-xl font-semibold text-gray-100">{{ title }}</h3>
      <p class="mt-2 text-gray-400">{{ message }}</p>
      <div class="mt-6 flex justify-end space-x-3">
        <button
          class="rounded-md border border-gray-600 bg-gray-700 px-4 py-2 text-sm font-medium text-gray-200 hover:bg-gray-600"
          @click="$emit('cancel')"
        >
          Cancel
        </button>
        <button
          :class="[
            'rounded-md px-4 py-2 text-sm font-medium text-white',
            actionType === 'approve' 
              ? 'bg-green-700 hover:bg-green-600' 
              : 'bg-red-700 hover:bg-red-600'
          ]"
          @click="$emit('confirm')"
        >
          {{ actionType === 'approve' ? 'Approve' : 'Reject' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: {
    type: Boolean,
    required: true
  },
  title: {
    type: String,
    required: true
  },
  message: {
    type: String,
    required: true
  },
  actionType: {
    type: String,
    default: 'approve',
    validator: (value) => ['approve', 'reject'].includes(value)
  }
});

defineEmits(['confirm', 'cancel']);
</script>