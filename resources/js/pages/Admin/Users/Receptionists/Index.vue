<script setup>
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { defineProps, onMounted } from 'vue';
import { route } from 'ziggy-js';

// Props for passing manager data

const props = defineProps({
    receptionists: {
        type: Object, // Changed from Array to Object since it's paginated
        default: () => ({ data: [] }),
    },
    is_admin: {
        type: Boolean,
    },
    manager: {
        type: Number,
    },
});
console.log(props.receptionists);

onMounted(() => {
    console.log('DOM is ready');
    console.log('Managers data:', props.receptionists);
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
            ${getInitials(receptionist.name)}
        </div>
    `;
};

const banManager = (id) => {
    if (confirm('Are you sure you want to change the ban status of this manager?')) {
        router.patch(route('admin.users.receptionists.ban', id), {
            onSuccess: () => {
                // Find and update the manager in the local data
                const receptionistIndex = props.receptionists.data.findIndex((m) => m.id === id);
                if (receptionistIndex !== -1) {
                    props.receptionists.data[receptionistIndex].is_banned = !props.receptionists.data[receptionistIndex].is_banned;
                }
            },
        });
    }
};

const confirmDelete = (id) => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(route('admin.users.receptionists.destroy', id));
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
const canManageReceptionist = (receptionist) => {
    // Check if user is admin
    const isAdmin = page.props.is_admin;

    // Check if the logged-in user is the manager of this receptionist
    const loggedInUserId = page.props.auth.user.id;
    const isManagerOfReceptionist = loggedInUserId === receptionist.manager_id;

    return isAdmin || isManagerOfReceptionist;
};
</script>

<template>
    <div class="min-h-screen rounded-lg bg-gray-900 p-8 text-gray-200 shadow-lg">
        <h1 class="mb-8 text-3xl font-bold">Manage Receptionists</h1>

        <!-- Add Manager Button -->
        <Link :href="route('admin.users.receptionists.create')">
            <Button class="mb-6">Add Receptionist</Button>
        </Link>

        <!-- Managers Table -->
        <Table v-if="receptionists && receptionists.data.length > 0" class="w-full overflow-hidden rounded-lg border border-gray-700">
            <TableHeader>
                <TableRow>
                    <TableHead>Name</TableHead>
                    <TableHead>Email</TableHead>
                    <TableHead>National ID</TableHead>
                    <TableHead>Avatar Image</TableHead>
                    <TableHead v-if="is_admin == true">Manager</TableHead>
                    <TableHead>Actions</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <TableRow v-for="receptionist in receptionists.data" :key="receptionist.id" class="transition hover:bg-gray-800">
                    <TableCell>{{ receptionist.name }}</TableCell>
                    <TableCell>{{ receptionist.email }}</TableCell>
                    <TableCell>{{ receptionist.national_id }}</TableCell>
                    <TableCell class="border-t border-gray-700 p-4">
                        <img
                            v-if="receptionist.avatar_img"
                            :src="getImageUrl(receptionist.avatar_img)"
                            alt="Avatar"
                            class="h-10 w-10 rounded-full border border-gray-600"
                            @="handleImageError"
                        />
                        <div v-else class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-300">
                            {{ getInitials(receptionist.name) }}
                        </div>
                    </TableCell>
                    <TableCell v-if="is_admin">
                        {{ receptionist.manager ? receptionist.manager.name : 'No Manager' }}
                    </TableCell>

                    <TableCell v-if="is_admin || receptionist.manager_id == manager">
                        <div class="flex gap-4">
                            <!-- Edit Button -->
                            <Link :href="route('admin.users.receptionists.edit', receptionist)">
                                <Button variant="outline">Edit</Button>
                            </Link>
                            <Link :href="route('admin.users.receptionists.show', receptionist)">
                                <Button variant="secondary">View</Button>
                            </Link>

                            <!-- Delete Button -->
                            <Link
                                method="delete"
                                :href="route('admin.users.receptionists.destroy', receptionist)"
                                as="button"
                                class="text-red-500 transition-colors duration-200 hover:text-red-400"
                                @click.prevent="confirmDelete(receptionist.id)"
                            >
                                <Button variant="destructive">Delete</Button>
                            </Link>
                            <Button @click="banManager(receptionist.id)" :variant="receptionist.is_banned ? 'default' : 'destructive'">
                                {{ receptionist.is_banned ? 'Unban Manager' : 'Ban Manager' }}
                            </Button>
                        </div>
                    </TableCell>
                </TableRow>

                <!-- Empty State -->
                <TableRow v-show="receptionists.length === 0">
                    <TableCell colspan="5" class="py-4 text-center text-gray-500">No receptionists found.</TableCell>
                </TableRow>
            </TableBody>
        </Table>
        <div class="mt-6 flex items-center justify-center gap-2">
            <Link
                v-for="page in receptionists.last_page"
                :key="page"
                :href="`?page=${page}`"
                class="rounded-lg px-4 py-2"
                :class="{
                    'bg-blue-600 text-white': page === receptionists.current_page,
                    'bg-gray-700 text-gray-300 hover:bg-gray-600': page !== receptionists.current_page,
                }"
            >
                {{ page }}
            </Link>
        </div>
    </div>
</template>
