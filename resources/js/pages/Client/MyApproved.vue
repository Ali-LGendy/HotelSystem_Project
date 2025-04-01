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
    if (confirm('Are you sure you want to change the approvement status of this client?')) {
        router.patch(route('client.approve', id), {
            onSuccess: () => {
                const clienttIndex = props.clients.data.findIndex((m) => m.id === id);
                if (clienttIndex !== -1) {
                    props.clients.data[clienttIndex].is_approved = !props.clients.data[clienttIndex].is_approved;
                }
            },
        });
    }
};

const confirmDelete = (id) => {
    if (confirm('Are you sure you want to delete this client?')) {
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

const getApprovedBadgeClass = (is_approved) => {
    switch (is_approved) {
        case true:
            return 'bg-blue-100 text-blue-800 dark:bg-green-900 dark:text-blue-200';
        case false:
            return 'bg-pink-100 text-pink-800 dark:bg-gray-900 dark:text-pink-200';
        default:
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200';
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

const banClient = () => {
    user.is_approved = !user.is_approved;
};
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-background p-8 text-foreground">
        <div class="w-full max-w-6xl">
            <Card class="w-full p-6">
                <CardHeader>
                    <div class="dark:bg-dark-700 flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-600">
                        <CardTitle class="text-3xl font-bold">My Approved Clients</CardTitle>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- Receptionists Table -->
                    <Table v-if="clients && clients.data.length > 0" class="w-full">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Mobile</TableHead>
                                <TableHead>Country</TableHead>
                                <TableHead>Avatar</TableHead>
                                <TableHead>Gender</TableHead>
                                <TableHead>Approvement</TableHead>
                                <TableHead>Actions</TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            <TableRow v-for="client in clients.data" :key="client.id" class="hover:bg-secondary/10">
                                <TableCell>{{ client.name }}</TableCell>
                                <TableCell>{{ client.email }}</TableCell>
                                <TableCell>{{ client.mobile }}</TableCell>
                                <TableCell>{{ client.country }}</TableCell>
                                <TableCell>
                                    <img
                                        v-if="client.avatar_img"
                                        :src="getImageUrl(client.avatar_img)"
                                        alt="Avatar"
                                        class="h-12 w-12 rounded-full object-cover"
                                        @error="(event) => handleImageError(event, client.name)"
                                    />
                                    <div v-else class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-300 text-foreground">
                                        {{ getInitials(client.name) }}
                                    </div>
                                </TableCell>
                                <TableCell class="whitespace-nowrap px-6 py-4 text-sm">
                                    <span
                                        :class="getGenderBadgeClass(client.gender)"
                                        class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                    >
                                        {{ client.gender }}
                                    </span>
                                </TableCell>
                                <TableCell class="whitespace-nowrap px-6 py-4 text-sm">
                                    <span
                                        :class="getApprovedBadgeClass(client.is_approved)"
                                        class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                    >
                                        {{ client.is_approved ? 'approved' : 'not approved' }}
                                    </span>
                                </TableCell>
                                <TableCell>
                                    <div class="flex gap-2">
                                        <Button @click="toggleApprove(client.id)" :variant="client.is_approved ? 'default' : 'destructive'">
                                            <!-- {{ client.is_approved ? 'Unapprove' : 'Aprrove' }} -->
                                            Unapprove
                                        </Button>
                                        <!-- View Button -->
                                        <!-- <Link :href="route('admin.users.receptionists.show', client)">
                                            <Button variant="secondary" class="h-10"> View </Button>
                                        </Link> -->
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Empty State -->
                    <div v-if="!clients.data.length" class="py-8 text-center text-muted-foreground">No Clients found.</div>

                    <!-- Pagination -->
                    <div class="mt-6 flex items-center justify-center gap-2">
                        <!-- First Page -->
                        <Link
                            :href="`?page=1`"
                            :class="{
                                'cursor-not-allowed opacity-50': clients.current_page === 1,
                                'hover:bg-secondary/80': clients.current_page !== 1,
                            }"
                            class="rounded-lg bg-secondary px-3 py-2 text-secondary-foreground transition-colors"
                        >
                            <span class="sr-only">First</span>
                            &laquo;
                        </Link>

                        <!-- Previous Page Arrow -->
                        <Link
                            :href="`?page=${Math.max(1, clients.current_page - 1)}`"
                            :class="{
                                'cursor-not-allowed opacity-50': clients.current_page === 1,
                                'hover:bg-secondary/80': clients.current_page !== 1,
                            }"
                            class="rounded-lg bg-secondary px-3 py-2 text-secondary-foreground transition-colors"
                        >
                            <span class="sr-only">Previous</span>
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
                                class="rounded-lg px-3 py-2 transition-colors"
                                :class="{
                                    'bg-primary text-primary-foreground': i === clients.current_page,
                                    'bg-secondary text-secondary-foreground hover:bg-secondary/80': i !== clients.current_page,
                                }"
                            >
                                {{ i }}
                            </Link>
                            <span
                                v-else-if="
                                    (i === 2 && clients.current_page > 3) ||
                                    (i === clients.last_page - 1 && clients.current_page < clients.last_page - 2)
                                "
                                class="px-1"
                            >
                                ...
                            </span>
                        </template>

                        <!-- Next Page Arrow -->
                        <Link
                            :href="`?page=${Math.min(clients.last_page, clients.current_page + 1)}`"
                            :class="{
                                'cursor-not-allowed opacity-50': clients.current_page === clients.last_page,
                                'hover:bg-secondary/80': clients.current_page !== clients.last_page,
                            }"
                            class="rounded-lg bg-secondary px-3 py-2 text-secondary-foreground transition-colors"
                        >
                            <span class="sr-only">Next</span>
                            &rarr;
                        </Link>

                        <!-- Last Page -->
                        <Link
                            :href="`?page=${clients.last_page}`"
                            :class="{
                                'cursor-not-allowed opacity-50': clients.current_page === clients.last_page,
                                'hover:bg-secondary/80': clients.current_page !== clients.last_page,
                            }"
                            class="rounded-lg bg-secondary px-3 py-2 text-secondary-foreground transition-colors"
                        >
                            <span class="sr-only">Last</span>
                            &raquo;
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
