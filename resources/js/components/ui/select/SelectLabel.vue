<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
  class: {
    type: String,
    default: '',
  },
});

const labelClasses = computed(() => {
  return `py-1.5 pl-8 pr-2 text-sm font-semibold ${props.class}`;
});
</script>

<template>
  <div :class="labelClasses">
    <slot />
  </div>
</template>