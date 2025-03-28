<script setup lang="ts">
import { DialogDescription } from '@/components/ui/dialog';
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
  class?: HTMLAttributes['class'];
}>();
</script>

<template>
  <DialogDescription :class="cn('', props.class)">
    <slot />
  </DialogDescription>
</template>