<script setup lang="ts">
import { computed, provide, ref, toRef, watch } from 'vue';

const props = defineProps({
  modelValue: {
    type: [String, Number, Object],
    default: undefined,
  },
  defaultValue: {
    type: [String, Number, Object],
    default: undefined,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  required: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:modelValue']);

const selectedValueRef = ref(props.modelValue || props.defaultValue);

watch(
  () => props.modelValue,
  (value) => {
    if (value !== undefined) {
      selectedValueRef.value = value;
    }
  }
);

const updateValue = (value: any) => {
  selectedValueRef.value = value;
  emit('update:modelValue', value);
};

// Provide values to child components
provide('select', {
  selectedValue: selectedValueRef,
  updateValue,
  disabled: toRef(props, 'disabled'),
  required: toRef(props, 'required'),
});
</script>

<template>
  <div class="relative">
    <slot />
  </div>
</template>