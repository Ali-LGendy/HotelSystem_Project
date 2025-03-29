<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="w-full max-w-md rounded-lg border border-border bg-background p-6 shadow-xl">
      <h3 class="text-xl font-semibold text-foreground">{{ title }}</h3>
      <p class="mt-2 text-muted-foreground">{{ message }}</p>
      <div class="mt-6 flex justify-end space-x-3">
        <button
          class="inline-flex items-center justify-center rounded-md border border-border bg-background px-4 py-2 text-sm font-medium text-foreground shadow-sm hover:bg-accent/10 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
          @click="$emit('cancel')"
          :disabled="processing"
        >
          Cancel
        </button>
        <button
          :class="[
            'inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-medium text-primary-foreground shadow hover:opacity-90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring',
            getActionButtonClass()
          ]"
          @click="$emit('confirm')"
          :disabled="processing"
        >
          <span v-if="processing" class="mr-2">
            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </span>
          {{ getActionButtonText() }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
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
    validator: (value) => ['approve', 'cancel', 'complete', 'delete'].includes(value)
  },
  processing: {
    type: Boolean,
    default: false
  }
});

defineEmits(['confirm', 'cancel']);

const getActionButtonClass = () => {
  switch (props.actionType) {
    case 'approve':
      return 'bg-green-600 hover:bg-green-700';
    case 'cancel':
    case 'delete':
      return 'bg-red-600 hover:bg-red-700';
    case 'complete':
      return 'bg-blue-600 hover:bg-blue-700';
    default:
      return 'bg-primary';
  }
};

const getActionButtonText = () => {
  switch (props.actionType) {
    case 'approve':
      return 'Approve';
    case 'cancel':
      return 'Cancel Reservation';
    case 'complete':
      return 'Complete';
    case 'delete':
      return 'Delete';
    default:
      return 'Confirm';
  }
};
</script>