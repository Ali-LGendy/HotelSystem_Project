<script setup lang="ts">
import { DialogTitle } from '@/components/ui/dialog';
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
  class?: HTMLAttributes['class'];
}>();
</script>

<template>
  <DialogTitle :class="cn('', props.class)">
    <slot />
  </DialogTitle>
</template>