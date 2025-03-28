<template>
    <div class="min-h-screen bg-background p-8 text-foreground">
        <div class="mx-auto max-w-7xl">
            <!-- Header Section with Title and Create Button -->
            <div class="mb-8 flex items-center justify-between">
                <h1 class="text-3xl font-bold text-foreground">Manage Managers</h1>
                <Link :href="route('admin.users.managers.create')">
                    <Button>+ Add Manager</Button>
                </Link>
            </div>

            <!-- Managers Table -->
            <Table v-if="managers && managers.data.length > 0" class="w-full rounded-lg border border-border">
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead>Email</TableHead>
                        <TableHead>National ID</TableHead>
                        <TableHead>Avatar</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow v-for="manager in managers.data" :key="manager.id" class="transition-colors hover:bg-accent/10">
                        <TableCell class="font-medium">{{ manager.name }}</TableCell>
                        <TableCell>{{ manager.email }}</TableCell>
                        <TableCell>{{ manager.national_id }}</TableCell>
                        <TableCell>
                            <img
                                v-if="manager.avatar_img"
                                :src="getImageUrl(manager.avatar_img)"
                                alt="Avatar"
                                class="h-10 w-10 rounded-full border border-border"
                                @error="handleImageError"
                            />
                            <div v-else class="flex h-10 w-10 items-center justify-center rounded-full bg-accent">
                                {{ getInitials(manager.name) }}
                            </div>
                        </TableCell>
                        <TableCell class="text-right">
                            <div class="flex justify-end space-x-2">
                                <Link :href="route('admin.users.managers.edit', manager)">
                                    <Button variant="outline">Edit</Button>
                                </Link>
                                <Link :href="route('admin.users.managers.show', manager)">
                                    <Button variant="secondary">View</Button>
                                </Link>
                                <Button @click="banManager(manager.id)" :variant="manager.is_banned ? 'default' : 'destructive'">
                                    {{ manager.is_banned ? 'Unban' : 'Ban' }}
                                </Button>
                                <Button variant="destructive" @click.prevent="confirmDelete(manager.id)"> Delete </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <!-- Empty State -->
            <div v-else class="rounded-lg bg-accent/50 p-8 text-center text-muted-foreground">
                No managers found. Create a new manager by clicking the "Add Manager" button.
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex items-center justify-center space-x-2">
                <Link
                    v-for="page in managers.last_page"
                    :key="page"
                    :href="`?page=${page}`"
                    class="rounded-lg px-4 py-2"
                    :class="{
                        'bg-primary text-primary-foreground': page === managers.current_page,
                        'bg-accent text-accent-foreground hover:bg-accent/80': page !== managers.current_page,
                    }"
                >
                    {{ page }}
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';

import { Link, router } from '@inertiajs/vue3';
import { defineProps, onMounted } from 'vue';
import { route } from 'ziggy-js';

import { usePage } from '@inertiajs/vue3';

function useCurrentUser() {
    const page = usePage();
    return page.props.auth.user;
}

const user = useCurrentUser();

const props = defineProps({
    managers: {
        type: Object,
        default: () => ({ data: [] }),
    },
});

onMounted(() => {
    console.log('DOM is ready');
    console.log('Managers data:', props.managers);
});

defineOptions({ layout: AppLayout });

const getImageUrl = (path) => {
    if (!path) return '/defaults/user.png';

    if (path.startsWith('http')) {
        return path;
    }

    return `/storage/${path}`;
};

const handleImageError = (event) => {
    const manager = event.target.closest('tr').__vnode.key;
    event.target.style.display = 'none';
    event.target.parentNode.innerHTML = `
        <div class="h-10 w-10 rounded-full bg-accent flex items-center justify-center">
            ${getInitials(props.managers.data.find((m) => m.id === manager).name)}
        </div>
    `;
};

const banManager = (id) => {
    if (confirm('Are you sure you want to change the ban status of this manager?')) {
        router.patch(route('admin.users.managers.ban', id), {
            onSuccess: () => {
                const managerIndex = props.managers.data.findIndex((m) => m.id === id);
                if (managerIndex !== -1) {
                    props.managers.data[managerIndex].is_banned = !props.managers.data[managerIndex].is_banned;
                }
            },
        });
    }
};

const confirmDelete = (id) => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(route('admin.users.managers.destroy', id));
    }
};

const getInitials = (name) => {
    return name
        .split(' ')
        .map((part) => part[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
};
</script>
