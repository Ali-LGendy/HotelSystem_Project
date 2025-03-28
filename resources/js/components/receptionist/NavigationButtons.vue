<template>
  <div class="flex flex-wrap gap-3">
    <button
      v-for="button in visibleButtons"
      :key="button.path"
      @click="navigateTo(button.path)"
      :class="[
        'inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-medium shadow transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50',
        button.primary 
          ? 'bg-primary text-primary-foreground hover:bg-primary/90' 
          : button.success
            ? 'bg-green-600 text-white hover:bg-green-700'
            : 'bg-secondary text-secondary-foreground hover:bg-secondary/80'
      ]"
    >
      {{ button.label }}
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  buttons: {
    type: Array,
    required: true
  },
  isAdmin: {
    type: Boolean,
    default: false
  }
});

const visibleButtons = computed(() => {
  return props.buttons.filter(button => !button.adminOnly || props.isAdmin);
});

const navigateTo = (url) => {
  router.visit(url, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
    onSuccess: () => {
      console.log('Navigation successful to:', url);
    },
    onError: (errors) => {
      console.error('Navigation error:', errors);
    }
  });
};
</script>