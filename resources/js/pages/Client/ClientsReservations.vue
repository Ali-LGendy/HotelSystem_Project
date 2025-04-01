<script setup>
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { defineProps } from 'vue';

function useCurrentUser() {
    const page = usePage();
    return page.props.auth.user;
}

const user = useCurrentUser();

const props = defineProps({
    reservations: {
        type: Object,
        default: () => ({ data: [] }),
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
                        <CardTitle class="text-3xl font-bold">Clients Reservations</CardTitle>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- Receptionists Table -->
                    <Table v-if="reservations && reservations.data.length > 0" class="w-full">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Accompany Number</TableHead>
                                <TableHead>Room Number</TableHead>
                                <TableHead>Paid Price</TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            <TableRow v-for="reservation in reservations.data" :key="reservation.id" class="hover:bg-secondary/10">
                                <TableCell>{{ reservation.client.name }}</TableCell>
                                <TableCell>{{ reservation.accompany_number }}</TableCell>
                                <TableCell>{{ reservation.room.room_number }}</TableCell>
                                <TableCell>{{ reservation.price_paid }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Empty State -->
                    <div v-if="!reservations.data.length" class="py-8 text-center text-muted-foreground">No Reservations found.</div>

                    <!-- Pagination -->
                    <div class="mt-6 flex items-center justify-center gap-2">
                        <!-- First Page -->
                        <Link
                            :href="`?page=1`"
                            :class="{
                                'cursor-not-allowed opacity-50': reservations.current_page === 1,
                                'hover:bg-secondary/80': reservations.current_page !== 1,
                            }"
                            class="rounded-lg bg-secondary px-3 py-2 text-secondary-foreground transition-colors"
                        >
                            <span class="sr-only">First</span>
                            &laquo;
                        </Link>

                        <!-- Previous Page Arrow -->
                        <Link
                            :href="`?page=${Math.max(1, reservations.current_page - 1)}`"
                            :class="{
                                'cursor-not-allowed opacity-50': reservations.current_page === 1,
                                'hover:bg-secondary/80': reservations.current_page !== 1,
                            }"
                            class="rounded-lg bg-secondary px-3 py-2 text-secondary-foreground transition-colors"
                        >
                            <span class="sr-only">Previous</span>
                            &larr;
                        </Link>

                        <!-- Page Numbers - Limited to 5 visible pages -->
                        <template v-for="i in reservations.last_page" :key="i">
                            <Link
                                v-if="
                                    i === 1 ||
                                    i === reservations.last_page ||
                                    (i >= reservations.current_page - 1 && i <= reservations.current_page + 1) ||
                                    (reservations.current_page <= 3 && i <= 5) ||
                                    (reservations.current_page >= reservations.last_page - 2 && i >= reservations.last_page - 4)
                                "
                                :href="`?page=${i}`"
                                class="rounded-lg px-3 py-2 transition-colors"
                                :class="{
                                    'bg-primary text-primary-foreground': i === reservations.current_page,
                                    'bg-secondary text-secondary-foreground hover:bg-secondary/80': i !== reservations.current_page,
                                }"
                            >
                                {{ i }}
                            </Link>
                            <span
                                v-else-if="
                                    (i === 2 && reservations.current_page > 3) ||
                                    (i === reservations.last_page - 1 && reservations.current_page < reservations.last_page - 2)
                                "
                                class="px-1"
                            >
                                ...
                            </span>
                        </template>

                        <!-- Next Page Arrow -->
                        <Link
                            :href="`?page=${Math.min(reservations.last_page, reservations.current_page + 1)}`"
                            :class="{
                                'cursor-not-allowed opacity-50': reservations.current_page === reservations.last_page,
                                'hover:bg-secondary/80': reservations.current_page !== reservations.last_page,
                            }"
                            class="rounded-lg bg-secondary px-3 py-2 text-secondary-foreground transition-colors"
                        >
                            <span class="sr-only">Next</span>
                            &rarr;
                        </Link>

                        <!-- Last Page -->
                        <Link
                            :href="`?page=${reservations.last_page}`"
                            :class="{
                                'cursor-not-allowed opacity-50': reservations.current_page === reservations.last_page,
                                'hover:bg-secondary/80': reservations.current_page !== reservations.last_page,
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
