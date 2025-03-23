<template>
  <span :class="badgeClass">
    <slot></slot>
  </span>
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'default'
  }
});

const badgeClass = computed(() => {
  const baseClass = 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium';
  const variantClass = {
    success: 'bg-green-100 text-green-800',
    info: 'bg-blue-100 text-blue-800',
    secondary: 'bg-gray-100 text-gray-800',
    warning: 'bg-yellow-100 text-yellow-800',
    destructive: 'bg-red-100 text-red-800',
    default: 'bg-gray-200 text-gray-800'
  }[props.variant];

  return `${baseClass} ${variantClass}`;
});
</script>

<style scoped>
/* Add any necessary styles here */
</style>
