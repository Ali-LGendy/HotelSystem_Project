<script setup lang="ts">
import { Dialog } from '@/components/ui/dialog';

const props = defineProps<{
  open?: boolean;
}>();

const emits = defineEmits(['update:open']);
</script>

<template>
  <Dialog :open="open" @update:open="emits('update:open', $event)">
    <slot />
  </Dialog>
</template>