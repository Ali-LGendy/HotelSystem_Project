<script setup lang="ts">
import { Check } from 'lucide-vue-next';
import { computed, inject, ref } from 'vue';

const props = defineProps({
  value: {
    type: [String, Number, Object],
    required: true,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  class: {
    type: String,
    default: '',
  },
});

const select = inject('select', {
  selectedValue: ref(undefined),
  updateValue: () => {},
  disabled: ref(false),
  required: ref(false),
});

const isSelected = computed(() => {
  return select.selectedValue.value === props.value;
});

const itemClasses = computed(() => {
  const baseClasses = 'relative flex w-full cursor-default select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm outline-none focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50';
  const selectedClasses = isSelected.value ? 'bg-accent text-accent-foreground' : '';
  const disabledClasses = props.disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer';
  return `${baseClasses} ${selectedClasses} ${disabledClasses} ${props.class}`;
});

const handleSelect = () => {
  if (props.disabled || select.disabled.value) return;
  select.updateValue(props.value);
};
</script>

<template>
  <div
    :class="itemClasses"
    :data-disabled="props.disabled || undefined"
    :data-selected="isSelected || undefined"
    @click="handleSelect"
  >
    <span v-if="isSelected" class="absolute left-2 flex h-3.5 w-3.5 items-center justify-center">
      <Check class="h-4 w-4" />
    </span>
    <slot />
  </div>
</template>