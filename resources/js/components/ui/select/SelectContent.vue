<script setup lang="ts">
import { ref, onMounted, onUnmounted, inject, computed } from 'vue';

const props = defineProps({
  class: {
    type: String,
    default: '',
  },
  position: {
    type: String,
    default: 'popper',
    validator: (value: string) => ['popper', 'item-aligned'].includes(value),
  },
});

const isOpen = ref(false);
const triggerRef = ref<HTMLElement | null>(null);
const contentRef = ref<HTMLElement | null>(null);

const select = inject('select', {
  selectedValue: ref(undefined),
  updateValue: () => {},
  disabled: ref(false),
  required: ref(false),
});

const contentClasses = computed(() => {
  const baseClasses = 'relative z-50 min-w-[8rem] overflow-hidden rounded-md border bg-popover text-popover-foreground shadow-md data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2';
  return `${baseClasses} ${props.class}`;
});

onMounted(() => {
  isOpen.value = true;
  document.addEventListener('click', handleOutsideClick);
});

onUnmounted(() => {
  document.removeEventListener('click', handleOutsideClick);
});

const handleOutsideClick = (event: MouseEvent) => {
  if (
    contentRef.value &&
    !contentRef.value.contains(event.target as Node) &&
    triggerRef.value &&
    !triggerRef.value.contains(event.target as Node)
  ) {
    isOpen.value = false;
  }
};
</script>

<template>
  <div
    v-if="isOpen"
    ref="contentRef"
    :class="contentClasses"
  >
    <div class="w-full p-1">
      <slot />
    </div>
  </div>
</template>