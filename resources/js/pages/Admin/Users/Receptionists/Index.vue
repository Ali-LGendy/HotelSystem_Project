<script setup>
import { Button } from '@/components/ui/button';
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
});

defineOptions({ layout: AppLayout });

const getImageUrl = (path) => {
    if (!path) return '/defaults/user.png';

    if (path.startsWith('http')) {
        return path;
    }

    return `/storage/${path}`;
};

const handleImageError = (event, name) => {
    event.target.style.display = 'none';
    event.target.parentNode.innerHTML = `
        <div class="h-10 w-10 rounded-full bg-accent flex items-center justify-center">
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
        router.delete(route('receptionist.destroy', id));
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

<template>
    <div class="min-h-screen bg-background p-8 text-foreground">
        <div class="mx-auto max-w-7xl">
            <!-- Header Section with Title and Create Button -->
            <div class="mb-8 flex items-center justify-between">
                <h1 class="text-3xl font-bold text-foreground">Manage Receptionists</h1>
                <Link :href="route('receptionist.create')">
                    <Button>+ Add Receptionist</Button>
                </Link>
            </div>

            <!-- Receptionists Table -->
            <Table v-if="receptionists && receptionists.data.length > 0" class="w-full rounded-lg border border-border">
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead>Email</TableHead>
                        <TableHead>National ID</TableHead>
                        <TableHead>Avatar</TableHead>
                        <TableHead v-if="user.roles.some((role) => role.name === 'admin')">Manager</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow 
                        v-for="receptionist in receptionists.data" 
                        :key="receptionist.id" 
                        class="transition-colors hover:bg-accent/10"
                    >
                        <TableCell class="font-medium">{{ receptionist.name }}</TableCell>
                        <TableCell>{{ receptionist.email }}</TableCell>
                        <TableCell>{{ receptionist.national_id }}</TableCell>
                        <TableCell>
                            <img
                                v-if="receptionist.avatar_img"
                                :src="getImageUrl(receptionist.avatar_img)"
                                alt="Avatar"
                                class="h-10 w-10 rounded-full border border-border"
                                @error="(event) => handleImageError(event, receptionist.name)"
                            />
                            <div v-else class="flex h-10 w-10 items-center justify-center rounded-full bg-accent">
                                {{ getInitials(receptionist.name) }}
                            </div>
                        </TableCell>
                        <TableCell v-if="user.roles.some((role) => role.name === 'admin')">
                            {{ receptionist.manager ? receptionist.manager.name : 'No Manager' }}
                        </TableCell>
                        <TableCell class="text-right">
                            <div class="flex justify-end space-x-2">
                                <Link :href="route('receptionist.edit', receptionist)">
                                    <Button 
                                        variant="outline"
                                        :disabled="!(user.roles.some((role) => role.name === 'admin') || receptionist.manager_id == user.id)"
                                    >
                                        Edit
                                    </Button>
                                </Link>
                                <Link :href="route('receptionist.show', receptionist)">
                                    <Button variant="secondary">View</Button>
                                </Link>
                                <Button 
                                    @click="banManager(receptionist.id)" 
                                    :variant="receptionist.is_banned ? 'default' : 'destructive'"
                                    :disabled="!(user.roles.some((role) => role.name === 'admin') || receptionist.manager_id == user.id)"
                                >
                                    {{ receptionist.is_banned ? 'Unban' : 'Ban' }}
                                </Button>
                                <Button 
                                    variant="destructive" 
                                    @click.prevent="confirmDelete(receptionist.id)"
                                    :disabled="!(user.roles.some((role) => role.name === 'admin') || receptionist.manager_id == user.id)"
                                >
                                    Delete
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <!-- Empty State -->
            <div v-else class="rounded-lg bg-accent/50 p-8 text-center text-muted-foreground">
                No receptionists found. Create a new receptionist by clicking the "Add Receptionist" button.
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex items-center justify-center space-x-2">
                <Link
                    v-for="page in receptionists.last_page"
                    :key="page"
                    :href="`?page=${page}`"
                    class="rounded-lg px-4 py-2"
                    :class="{
                        'bg-primary text-primary-foreground': page === receptionists.current_page,
                        'bg-accent text-accent-foreground hover:bg-accent/80': page !== receptionists.current_page,
                    }"
                >
                    {{ page }}
                </Link>
            </div>
        </div>
    </div>
</template>