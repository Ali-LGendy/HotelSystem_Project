<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
  class?: HTMLAttributes['class'];
  variant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link';
}>();
</script>

<template>
  <Button :variant="variant || 'default'" :class="cn('', props.class)">
    <slot />
  </Button>
</template>