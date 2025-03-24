<template>
  <button :class="buttonClass" @click="$emit('click')">
    <slot></slot>
  </button>
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'default'
  },
  size: {
    type: String,
    default: 'md'
  }
});

const buttonClass = computed(() => {
  const baseClass = 'px-4 py-2 rounded';
  const variantClass = {
    default: 'bg-gray-300 text-black',
    outline: 'border border-gray-300 text-black',
    destructive: 'bg-red-500 text-white'
  }[props.variant];
  const sizeClass = {
    sm: 'text-sm',
    md: 'text-base',
    lg: 'text-lg'
  }[props.size];

  return `${baseClass} ${variantClass} ${sizeClass}`;
});
</script>

<style scoped>
/* Add any necessary styles here */
</style>
