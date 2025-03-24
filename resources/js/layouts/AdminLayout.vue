<script setup>
import { Link } from '@inertiajs/vue3';
import { defineProps } from 'vue';

const props = defineProps({
    menuLinks: Array,
});
</script>

<template>
    <div class="flex">
        <!-- Sidebar -->
        <aside class="min-h-screen w-1/4 bg-gray-800 p-4 text-white">
            <h2 class="mb-4 text-xl font-bold">Admin Dashboard</h2>

            <!-- Dynamic Links -->
            <ul>
                <li v-for="link in menuLinks" :key="link.name" class="mb-2">
                    <Link :href="link.route" class="block rounded bg-gray-700 p-2 hover:bg-gray-600">
                        {{ link.name }}
                    </Link>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="w-3/4 p-6">
            <slot />
        </main>
    </div>
</template>
