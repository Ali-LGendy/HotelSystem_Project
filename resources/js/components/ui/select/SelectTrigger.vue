<script setup lang="ts">
import { ChevronDown } from 'lucide-vue-next';
import { inject, ref, computed } from 'vue';

const props = defineProps({
  placeholder: {
    type: String,
    default: 'Select an option',
  },
  class: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['click']);

const select = inject('select', {
  selectedValue: ref(undefined),
  updateValue: () => {},
  disabled: ref(false),
  required: ref(false),
});

const buttonClasses = computed(() => {
  return `flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 ${props.class}`;
});

const handleClick = (event: MouseEvent) => {
  if (select.disabled.value) return;
  emit('click', event);
};
</script>

<template>
  <button
    type="button"
    :class="buttonClasses"
    :disabled="select.disabled"
    @click="handleClick"
  >
    <slot />
    <ChevronDown class="h-4 w-4 opacity-50" />
  </button>
</template>