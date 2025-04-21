<script setup>
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { defineProps,watch } from 'vue';
import { route } from 'ziggy-js';
import { useToast } from 'vue-toastification';
const toast = useToast();
const page = usePage();
const flash = page.props?.flash;
console.log(page.props);
console.log(flash);


function useCurrentUser() {
    return page.props.auth.user;
}

const user = useCurrentUser();

const props = defineProps({
    clients: {
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

const toggleApprove = (id) => {
    // if (confirm('Are you sure you want to change the approvement status of this client?')) {
        router.patch(route('client.approve', id), {
            onSuccess: () => {
                toast.success('User approved successfully!');
                const clienttIndex = props.clients.data.findIndex((m) => m.id === id);
                if (clienttIndex !== -1) {
                    props.clients.data[clienttIndex].is_approved = !props.clients.data[clienttIndex].is_approved;
                }
            },
        });
    // }
};

const confirmDelete = (id) => {
    if (confirm('Are you sure you want to delete this client?')) {
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

const getApprovedBadgeClass = (is_approved) => {
    switch (is_approved) {
        case true:
            return 'bg-blue-100 text-blue-800 dark:bg-green-900 dark:text-blue-200';
        case false:
            return 'bg-pink-100 text-pink-800 dark:bg-gray-900 dark:text-pink-200';
        default:
            return 'bg-grey-100 text-purple-800 dark:bg-grey-900 dark:text-purple-200';
    }
};
const getGenderBadgeClass = (gender) => {
    switch (gender) {
        case 'male':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
        case 'female':
            return 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200';
        default:
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200';
    }
};
const banManager = (id) => {
    if (confirm('Are you sure you want to change the ban status of this receptionist?')) {
        router.patch(route('client.ban', id), {
            onSuccess: () => {
                const clientIndex = props.clients.data.findIndex((m) => m.id === id);
                if (clientIndex !== -1) {
                    props.clients.data[clientIndex].is_banned = !props.clients.data[clientIndex].is_banned;
                }
            },
        });
    }
}; 

watch(
  () => page.props?.flash,
  (flash) => {
    if (flash?.success) {
      toast.success(flash.success);
    }

    if (flash?.error) {
      toast.error(flash.error);
    }
  },
  { immediate: true }
);
</script>
<template>
    <div class="min-h-screen bg-background p-8 text-foreground">
        <div class="mx-auto max-w-7xl">
            <!-- Header Section with Title and Create Button -->
            <div class="mb-8 flex items-center justify-between">
                <h1 class="text-3xl font-bold text-foreground">Manage Clients</h1>
                <Link v-if="user.roles.some((role) => role.name === 'admin')" :href="route('client.create')">
                    <Button>+ Add Client</Button>
                </Link>
            </div>

            <!-- Clients Table -->
            <Table v-if="clients && clients.data.length > 0" class="w-full rounded-lg border border-border">
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead>Email</TableHead>
                        <TableHead>Mobile</TableHead>
                        <TableHead>Country</TableHead>
                        <TableHead>Avatar</TableHead>
                        <TableHead>Gender</TableHead>
                        <TableHead>Approval</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow v-for="client in clients.data" :key="client.id" class="transition-colors hover:bg-accent/10">
                        <TableCell class="font-medium">{{ client.name }}</TableCell>
                        <TableCell>{{ client.email }}</TableCell>
                        <TableCell>{{ client.mobile }}</TableCell>
                        <TableCell>{{ client.country }}</TableCell>
                        <TableCell>
                            <img
                                v-if="client.avatar_img"
                                :src="getImageUrl(client.avatar_img)"
                                alt="Avatar"
                                class="h-10 w-10 rounded-full border border-border"
                                @error="(event) => handleImageError(event, client.name)"
                            />
                            <div v-else class="flex h-10 w-10 items-center justify-center rounded-full bg-accent">
                                {{ getInitials(client.name) }}
                            </div>
                        </TableCell>
                        <TableCell>
                            <span :class="getGenderBadgeClass(client.gender)" class="inline-flex rounded-full px-2 text-xs font-semibold leading-5">
                                {{ client.gender }}
                            </span>
                        </TableCell>
                        <TableCell>
                            <span
                                :class="getApprovedBadgeClass(client.is_approved)"
                                class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                            >
                                {{ client.is_approved ? 'approved' : 'not approved' }}
                            </span>
                        </TableCell>
                        <TableCell class="text-right">
                            <div class="flex justify-end space-x-2">
                                <!-- Edit Button -->
                                <Link v-if="user.roles.some((role) => role.name === 'admin')" :href="route('client.edit', client)">
                                    <Button variant="outline">Edit</Button>
                                </Link>

                                <!-- View Button -->
                                <Link :href="route('client.show', client)">
                                    <Button variant="secondary">View</Button>
                                </Link>

                                <!-- Approve Button -->
                                <Button v-if="!client.is_approved" @click="toggleApprove(client.id)" :variant="client.is_approved ? 'secondary' : 'default'">
                                    {{'Approve'}}
                                </Button>

                                <!-- Ban/Unban Button -->
                                <Button
                                    v-if="user.roles.some((role) => role.name === 'admin') && client.is_approved"
                                    @click="banManager(client.id)"
                                    :variant="client.is_banned ? 'default' : 'destructive'"
                                >
                                    {{ client.is_banned ? 'Unban' : 'Ban' }}
                                </Button>

                                <!-- Delete Button -->
                                <Button
                                    v-if="user.roles.some((role) => role.name === 'admin')"
                                    variant="destructive"
                                    @click="confirmDelete(client.id)"
                                >
                                    Delete
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <!-- Empty State -->
            <div v-if="!clients.data.length" class="rounded-lg bg-accent/50 p-8 text-center text-muted-foreground">No clients found.</div>

            <!-- Pagination - Similar to managers but with improved functionality -->
            <div class="mt-6 flex items-center justify-center space-x-2">
                <!-- First Page -->
                <Link
                    :href="`?page=1`"
                    :class="{
                        'cursor-not-allowed opacity-50': clients.current_page === 1,
                        'bg-accent text-accent-foreground hover:bg-accent/80': clients.current_page !== 1,
                    }"
                    class="rounded-lg px-3 py-2 text-sm"
                >
                    &laquo;
                </Link>

                <!-- Previous Page -->
                <Link
                    :href="`?page=${Math.max(1, clients.current_page - 1)}`"
                    :class="{
                        'cursor-not-allowed opacity-50': clients.current_page === 1,
                        'bg-accent text-accent-foreground hover:bg-accent/80': clients.current_page !== 1,
                    }"
                    class="rounded-lg px-3 py-2 text-sm"
                >
                    &larr;
                </Link>

                <!-- Page Numbers - Limited to 5 visible pages -->
                <template v-for="i in clients.last_page" :key="i">
                    <Link
                        v-if="
                            i === 1 ||
                            i === clients.last_page ||
                            (i >= clients.current_page - 1 && i <= clients.current_page + 1) ||
                            (clients.current_page <= 3 && i <= 5) ||
                            (clients.current_page >= clients.last_page - 2 && i >= clients.last_page - 4)
                        "
                        :href="`?page=${i}`"
                        class="rounded-lg px-4 py-2"
                        :class="{
                            'bg-primary text-primary-foreground': i === clients.current_page,
                            'bg-accent text-accent-foreground hover:bg-accent/80': i !== clients.current_page,
                        }"
                    >
                        {{ i }}
                    </Link>
                    <span
                        v-else-if="
                            (i === 2 && clients.current_page > 3) || (i === clients.last_page - 1 && clients.current_page < clients.last_page - 2)
                        "
                        class="px-1"
                    >
                        ...
                    </span>
                </template>

                <!-- Next Page -->
                <Link
                    :href="`?page=${Math.min(clients.last_page, clients.current_page + 1)}`"
                    :class="{
                        'cursor-not-allowed opacity-50': clients.current_page === clients.last_page,
                        'bg-accent text-accent-foreground hover:bg-accent/80': clients.current_page !== clients.last_page,
                    }"
                    class="rounded-lg px-3 py-2 text-sm"
                >
                    &rarr;
                </Link>

                <!-- Last Page -->
                <Link
                    :href="`?page=${clients.last_page}`"
                    :class="{
                        'cursor-not-allowed opacity-50': clients.current_page === clients.last_page,
                        'bg-accent text-accent-foreground hover:bg-accent/80': clients.current_page !== clients.last_page,
                    }"
                    class="rounded-lg px-3 py-2 text-sm"
                >
                    &raquo;
                </Link>
            </div>
        </div>
    </div>
</template>
