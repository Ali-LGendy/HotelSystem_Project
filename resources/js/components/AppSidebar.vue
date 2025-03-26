<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid } from 'lucide-vue-next';
import { defineProps } from 'vue';
import AppLogo from './AppLogo.vue';
// interface MenuLink {
//     name: string;
//     route: string;
// }

// Accept the menuLinks prop
defineProps<{
    menuLinks: NavItem[];
}>();
const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboardd',
        icon: LayoutGrid,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <!-- <NavMain :items="mainNavItems" /> -->
            <NavMain :items="menuLinks" />
            <!-- <h4>HIIIIIIIIIIIIIIIIIIIIIIIIIIIII</h4> -->
            <!-- <aside class="min-h-screen w-1/4 bg-gray-800 p-4 text-white"> -->
            <h2 class="mb-4 text-xl font-bold">Admin Dashboard</h2>

            <!-- Dynamic Links -->
            <ul>
                <li v-for="link in menuLinks" :key="link.name" class="mb-2">
                    <Link :href="link.route" class="block rounded bg-gray-700 p-2 hover:bg-gray-600">
                        {{ link.name }}
                    </Link>
                </li>
            </ul>
            <!-- </aside> -->
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
