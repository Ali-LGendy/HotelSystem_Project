<script setup lang="ts">
import { SidebarProvider } from '@/components/ui/sidebar';
import { ToastManager } from '@/components/ui/toast';
import { onMounted, ref } from 'vue';

interface Props {
    variant?: 'header' | 'sidebar';
}

defineProps<Props>();

const isOpen = ref(true);
const toastManager = ref(null);

onMounted(() => {
    isOpen.value = localStorage.getItem('sidebar') !== 'false';
});

const handleSidebarChange = (open: boolean) => {
    isOpen.value = open;
    localStorage.setItem('sidebar', String(open));
};

// Expose toast manager to the global window for easy access
defineExpose({
    toast: toastManager
});
</script>

<template>
    <div v-if="variant === 'header'" class="flex min-h-screen w-full flex-col">
        <slot />
        <ToastManager ref="toastManager" />
    </div>
    <SidebarProvider v-else :default-open="isOpen" :open="isOpen" @update:open="handleSidebarChange">
        <slot />
        <ToastManager ref="toastManager" />
    </SidebarProvider>
</template>
