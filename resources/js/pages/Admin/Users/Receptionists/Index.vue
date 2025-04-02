<script setup>
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { defineProps } from 'vue';
import { route } from 'ziggy-js';

function useCurrentUser() {
    const page = usePage();
    return page.props.auth.user;
}

const user = useCurrentUser();

const props = defineProps({
    receptionists: {
        type: Object,
        default: () => ({ data: [] }),
    },
    is_admin: {
        type: Boolean,
    },
    user_id: {
        type: Number,
    },
});

defineOptions({ layout: AppLayout });

const getImageUrl = (path) => {
    if (!path) return '/defaults/user.png';

    // For full URLs
    if (path.startsWith('http')) {
        return path;
    }

    // For local storage files
    return `/storage/${path}`;
};

// Fallback to initials if image fails to load
const handleImageError = (event, name) => {
    event.target.style.display = 'none';
    event.target.parentNode.innerHTML = `
        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
            ${getInitials(name)}
        </div>
    `;
};

const banManager = (id) => {
    if (confirm('Are you sure you want to change the ban status of this receptionist?')) {
        router.patch(route('receptionist.ban', id), {
            onSuccess: () => {
                const receptionistIndex = props.receptionists.data.findIndex((m) => m.id === id);
                if (receptionistIndex !== -1) {
                    props.receptionists.data[receptionistIndex].is_banned = !props.receptionists.data[receptionistIndex].is_banned;
                }
            },
        });
    }
};

const confirmDelete = (id) => {
    if (confirm('Are you sure you want to delete this receptionist?')) {
        router.delete(route('client.destroy', id));
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
    <div class="flex min-h-screen items-center justify-center bg-background p-8 text-foreground">
        <div class="w-full max-w-6xl">
            <Card class="w-full p-6">
                <CardHeader>
                    <div class="dark:bg-dark-700 flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-600">
                        <CardTitle class="text-3xl font-bold">Manage Receptionists</CardTitle>

                        <Link :href="route('receptionist.create')">
                            <Button class="h-12 text-lg">Add Receptionist</Button>
                        </Link>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- Add Receptionist Button -->

                    <!-- Receptionists Table -->
                    <Table v-if="receptionists && receptionists.data.length > 0" class="w-full">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>National ID</TableHead>
                                <TableHead>Avatar</TableHead>
                                <TableHead v-if="user.roles.some((role) => role.name === 'admin')">Manager</TableHead>
                                <TableHead>Actions</TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            <TableRow v-for="receptionist in receptionists.data" :key="receptionist.id" class="hover:bg-secondary/10">
                                <TableCell>{{ receptionist.name }}</TableCell>
                                <TableCell>{{ receptionist.email }}</TableCell>
                                <TableCell>{{ receptionist.national_id }}</TableCell>
                                <TableCell>
                                    <img
                                        v-if="receptionist.avatar_img"
                                        :src="getImageUrl(receptionist.avatar_img)"
                                        alt="Avatar"
                                        class="h-12 w-12 rounded-full object-cover"
                                        @error="(event) => handleImageError(event, receptionist.name)"
                                    />
                                    <div v-else class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-300 text-foreground">
                                        {{ getInitials(receptionist.name) }}
                                    </div>
                                </TableCell>
                                <TableCell v-if="user.roles.some((role) => role.name === 'admin')">
                                    {{ receptionist.manager ? receptionist.manager.name : 'No Manager' }}
                                </TableCell>

                                <TableCell>
                                    <div class="flex gap-2">
                                        <!-- Edit Button -->
                                        <Link :href="route('receptionist.edit', receptionist)">
                                            <Button
                                                variant="outline"
                                                :disabled="!(user.roles.some((role) => role.name === 'admin') || receptionist.manager_id == user.id)"
                                                class="h-10"
                                            >
                                                Edit
                                            </Button>
                                        </Link>

                                        <!-- View Button -->
                                        <Link :href="route('receptionist.show', receptionist)">
                                            <Button variant="secondary" class="h-10"> View </Button>
                                        </Link>

                                        <!-- Delete Button -->
                                        <Button
                                            variant="destructive"
                                            @click="confirmDelete(receptionist.id)"
                                            :disabled="!(user.roles.some((role) => role.name === 'admin') || receptionist.manager_id == user.id)"
                                            class="h-10"
                                        >
                                            Delete
                                        </Button>

                                        <!-- Ban/Unban Button -->
                                        <Button
                                            @click="banManager(receptionist.id)"
                                            :variant="receptionist.is_banned ? 'default' : 'destructive'"
                                            :disabled="!(user.roles.some((role) => role.name === 'admin') || receptionist.manager_id == user.id)"
                                            class="h-10"
                                        >
                                            {{ receptionist.is_banned ? 'Unban' : 'Ban' }}
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Empty State -->
                    <div v-if="!receptionists.data.length" class="py-8 text-center text-muted-foreground">No receptionists found.</div>

                    <!-- Pagination -->
                    <div class="mt-6 flex items-center justify-center gap-2">
                        <Link
                            v-for="page in receptionists.last_page"
                            :key="page"
                            :href="`?page=${page}`"
                            class="rounded-lg px-4 py-2 transition-colors"
                            :class="{
                                'bg-primary text-primary-foreground': page === receptionists.current_page,
                                'bg-secondary text-secondary-foreground hover:bg-secondary/80': page !== receptionists.current_page,
                            }"
                        >
                            {{ page }}
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
