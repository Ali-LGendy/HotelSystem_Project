<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
  class: {
    type: String,
    default: '',
  },
});

const separatorClasses = computed(() => {
  return `-mx-1 my-1 h-px bg-muted ${props.class}`;
});
</script>

<template>
  <div :class="separatorClasses" />
</template>