<script setup lang="ts">
import { DialogFooter } from '@/components/ui/dialog';
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
  class?: HTMLAttributes['class'];
}>();
</script>

<template>
  <DialogFooter :class="cn('', props.class)">
    <slot />
  </DialogFooter>
</template>