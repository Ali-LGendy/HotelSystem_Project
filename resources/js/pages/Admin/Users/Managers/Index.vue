<script setup>
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { defineProps, onMounted } from 'vue';
import { route } from 'ziggy-js';

// Props for passing manager data

const props = defineProps({
    managers: {
        type: Object, // Changed from Array to Object since it's paginated
        default: () => ({ data: [] }),
    },
});
onMounted(() => {
    console.log('DOM is ready');
    console.log('Managers data:', props.managers);
});
defineOptions({ layout: AppLayout });

const getImageUrl = (path) => {
    if (!path) return '/dafaults/user.png';

    // For full URLs
    if (path.startsWith('http')) {
        return path;
    }

    // For local storage files
    return `/storage/${path}`;
};

// Fallback to initials if image fails to load
const handleImageError = (event) => {
    event.target.style.display = 'none';
    event.target.parentNode.innerHTML = `
        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
            ${getInitials(manager.name)}
        </div>
    `;
};

const banManager = (id) => {
    if (confirm('Are you sure you want to change the ban status of this manager?')) {
        router.patch(route('admin.users.managers.ban', id), {
            onSuccess: () => {
                // Find and update the manager in the local data
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
// Get initials from name
const getInitials = (name) => {
    return name
        .split(' ')
        .map((part) => part[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
};
</script>

<template>
    <div class="min-h-screen rounded-lg bg-gray-900 p-8 text-gray-200 shadow-lg">
        <h1 class="mb-8 text-3xl font-bold">Manage Managers</h1>

        <!-- Add Manager Button -->
        <Link :href="route('admin.users.managers.create')">
            <Button class="mb-6">Add Manager</Button>
        </Link>

        <!-- Managers Table -->
        <Table v-if="managers && managers.data.length > 0" class="w-full overflow-hidden rounded-lg border border-gray-700">
            <TableHeader>
                <TableRow>
                    <TableHead>Name</TableHead>
                    <TableHead>Email</TableHead>
                    <TableHead>Password</TableHead>
                    <TableHead>National ID</TableHead>
                    <TableHead>Avatar Image</TableHead>
                    <TableHead>Actions</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <TableRow v-for="manager in managers.data" :key="manager.id" class="transition hover:bg-gray-800">
                    <TableCell>{{ manager.name }}</TableCell>
                    <TableCell>{{ manager.email }}</TableCell>
                    <TableCell>{{ manager.password }}</TableCell>
                    <TableCell>{{ manager.national_id }}</TableCell>
                    <TableCell class="border-t border-gray-700 p-4">
                        <img
                            v-if="manager.avatar_img"
                            :src="getImageUrl(manager.avatar_img)"
                            alt="Avatar"
                            class="h-10 w-10 rounded-full border border-gray-600"
                            @="handleImageError"
                        />
                        <div v-else class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-300">
                            {{ getInitials(manager.name) }}
                        </div>
                    </TableCell>
                    <TableCell>
                        <div class="flex gap-4">
                            <!-- Edit Button -->
                            <Link :href="route('admin.users.managers.edit', manager)">
                                <Button variant="outline">Edit</Button>
                            </Link>

                            <!-- Delete Button -->
                            <Link
                                method="delete"
                                :href="route('admin.users.managers.destroy', manager)"
                                as="button"
                                class="text-red-500 transition-colors duration-200 hover:text-red-400"
                                @click.prevent="confirmDelete(manager.id)"
                            >
                                <Button variant="destructive">Delete</Button>
                            </Link>
                            <Button @click="banManager(manager.id)" :variant="manager.is_banned ? 'default' : 'destructive'">
                                {{ manager.is_banned ? 'Unban Manager' : 'Ban Manager' }}
                            </Button>
                        </div>
                    </TableCell>
                </TableRow>

                <!-- Empty State -->
                <TableRow v-show="managers.length === 0">
                    <TableCell colspan="5" class="py-4 text-center text-gray-500">No managers found.</TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
