<script setup lang="ts">
import { inject, ref } from 'vue';

const props = defineProps({
  placeholder: {
    type: String,
    default: 'Select an option',
  },
});

const select = inject('select', {
  selectedValue: ref(undefined),
  updateValue: () => {},
  disabled: ref(false),
  required: ref(false),
});
</script>

<template>
  <span class="block truncate">
    <slot v-if="select.selectedValue">{{ select.selectedValue }}</slot>
    <span v-else class="text-muted-foreground">{{ placeholder }}</span>
  </span>
</template>