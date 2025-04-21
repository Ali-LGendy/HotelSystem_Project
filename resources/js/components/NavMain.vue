<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData, type LucideIcon } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
  Users,
  UserCog,
  UserPlus,
  UserCheck,
  Building2,
  DoorClosed,
  CalendarCheck,
} from 'lucide-vue-next';

const iconMap = {
  Users,
  UserCog,
  UserPlus,
  UserCheck,
  Building2,
  DoorClosed,
  CalendarCheck,
};

defineProps<{
    items: NavItem[];
}>();

const page = usePage<SharedData>();

</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton as-child :is-active="item.href === page.url">
                    <Link :href="item.href">
                        <component 
                        v-if="item.icon && iconMap[item.icon]"
                        :is="iconMap[item.icon]"
                         />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
