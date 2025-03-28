<script setup lang="ts">
import { DialogContent } from '@/components/ui/dialog';
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
  class?: HTMLAttributes['class'];
}>();
</script>

<template>
  <DialogContent :class="cn('sm:max-w-[425px]', props.class)">
    <slot />
  </DialogContent>
</template>