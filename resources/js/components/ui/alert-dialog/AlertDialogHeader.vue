<script setup lang="ts">
import { DialogHeader } from '@/components/ui/dialog';
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
  class?: HTMLAttributes['class'];
}>();
</script>

<template>
  <DialogHeader :class="cn('', props.class)">
    <slot />
  </DialogHeader>
</template>