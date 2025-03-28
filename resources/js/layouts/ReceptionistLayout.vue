<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

// Define menu links for the receptionist sidebar based on their permissions
const menuLinks = [
    { name: 'Manage Clients', route: '/receptionist/clients' },
    { name: 'My Approved Clients', route: '/receptionist/clients/my-approved' },
    { name: 'Clients Reservations', route: '/receptionist/clients/reservations' },
    { name: 'Pending Reservations', route: '/receptionist/reservations' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs" :menuLinks="menuLinks">
        <slot />
    </AppLayout>
</template>