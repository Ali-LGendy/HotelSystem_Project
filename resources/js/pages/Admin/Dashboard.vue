<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

import { defineProps } from 'vue';
const props = defineProps({
    menuLinks: Array,
});
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];
const frameworks = [
    { name: 'Vite', icon: '⚡' },
    { name: 'Nuxt', icon: '▲' },
    { name: 'Astro', icon: 'A' },
    { name: 'Laravel', icon: 'L' },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs" :menuLinks="props.menuLinks">
        <!-- <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min">
                <PlaceholderPattern />
            </div>
        </div> -->
        <div class="min-h-screen bg-gray-900 p-8 text-white">
            <h2 class="mb-6 text-xl font-semibold">Frameworks</h2>

            <div class="grid grid-cols-2 gap-6">
                <div
                    v-for="framework in frameworks"
                    :key="framework.name"
                    class="flex items-center justify-center rounded-lg border border-gray-700 p-6 transition hover:bg-gray-800"
                >
                    <span class="mr-4 text-4xl">{{ framework.icon }}</span>
                    <span class="text-lg">{{ framework.name }}</span>
                </div>
            </div>

            <h2 class="mt-10 text-xl font-semibold">TypeScript</h2>
        </div>
    </AppLayout>
</template>
