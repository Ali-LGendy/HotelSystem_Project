<script setup lang="ts">

import { useAppearance } from '@/composables/useAppearance';
import { Moon, Sun } from 'lucide-vue-next';

const { appearance, updateAppearance } = useAppearance();

const toggleTheme = () => {
  // Only toggle between light and dark
  updateAppearance(appearance.value === 'dark' ? 'light' : 'dark');
};
</script>

<template>
  <button
    @click="toggleTheme"
    class="rounded-md p-2 hover:bg-neutral-200/60 dark:hover:bg-neutral-700/60 transition-colors"
    :title="appearance === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'"
  >
    <Sun 
      v-if="appearance === 'dark'" 
      class="h-5 w-5 text-neutral-500 dark:text-neutral-400" 
    />
    <Moon 
      v-else 
      class="h-5 w-5 text-neutral-500 dark:text-neutral-400" 
    />
    <span class="sr-only">{{ appearance === 'dark' ? 'Switch to light mode' : 'Switch to dark mode' }}</span>
  </button>
</template>