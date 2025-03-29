<template>
    <div class="min-h-screen bg-background text-foreground p-8">
        <div class="mx-auto max-w-7xl">
            <!-- Header with Navigation -->
            <div class="mb-8 flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <h1 class="text-3xl font-bold">Edit Client</h1>
                    <p class="mt-2 text-muted-foreground">Update client information</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <Link
                        href="/receptionist/clients/all"
                        class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                    >
                        Back to All Clients
                    </Link>
                    <Link
                        href="/receptionist/clients"
                        class="inline-flex items-center justify-center rounded-md bg-secondary px-4 py-2 text-sm font-medium text-secondary-foreground shadow-sm transition-colors hover:bg-secondary/80 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                    >
                        Pending Clients
                    </Link>
                </div>
            </div>

            <!-- Edit Client Form -->
            <div class="rounded-lg border border-border bg-card p-6 shadow-sm">
                <form @submit.prevent="updateClient">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium">Name</label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                required
                            />
                            <div v-if="form.errors.name" class="mt-1 text-sm text-destructive">{{ form.errors.name }}</div>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium">Email</label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                required
                            />
                            <div v-if="form.errors.email" class="mt-1 text-sm text-destructive">{{ form.errors.email }}</div>
                        </div>

                        <!-- Mobile -->
                        <div>
                            <label for="mobile" class="block text-sm font-medium">Mobile</label>
                            <input
                                id="mobile"
                                v-model="form.mobile"
                                type="text"
                                class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                            />
                            <div v-if="form.errors.mobile" class="mt-1 text-sm text-destructive">{{ form.errors.mobile }}</div>
                        </div>

                        <!-- Country -->
                        <div>
                            <label for="country" class="block text-sm font-medium">Country</label>
                            <input
                                id="country"
                                v-model="form.country"
                                type="text"
                                class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                            />
                            <div v-if="form.errors.country" class="mt-1 text-sm text-destructive">{{ form.errors.country }}</div>
                        </div>

                        <!-- Gender -->
                        <div>
                            <label for="gender" class="block text-sm font-medium">Gender</label>
                            <select
                                id="gender"
                                v-model="form.gender"
                                class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                            >
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <div v-if="form.errors.gender" class="mt-1 text-sm text-destructive">{{ form.errors.gender }}</div>
                        </div>

                        <!-- Status Options -->
                        <div>
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center">
                                    <input
                                        id="is_approved"
                                        v-model="form.is_approved"
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-primary text-primary focus:ring-primary"
                                    />
                                    <label for="is_approved" class="ml-2 block text-sm">Approved</label>
                                </div>
                                <!-- Commented out banned checkbox preserved -->
                                <!-- <div class="flex items-center">
                  <input
                    id="is_banned"
                    v-model="form.is_banned"
                    type="checkbox"
                    class="h-4 w-4 rounded border-primary text-primary focus:ring-primary"
                  />
                  <label for="is_banned" class="ml-2 block text-sm">Banned</label>
                </div> -->
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-6 flex justify-end">
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
                        >
                            {{ form.processing ? 'Updating...' : 'Update Client' }}
                        </Button>
                    </div>
                </form>
            </div>

            <!-- Client Reservations Section -->
            <div v-if="client.reservations && client.reservations.length > 0" class="mt-8">
                <h3 class="mb-4 text-xl font-semibold">Client Reservations</h3>
                <div class="overflow-hidden rounded-lg border border-border">
                    <Table class="w-full">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Room</TableHead>
                                <TableHead>Check-in</TableHead>
                                <TableHead>Check-out</TableHead>
                                <TableHead>Status</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="reservation in client.reservations" :key="reservation.id" class="hover:bg-accent/10">
                                <TableCell class="font-medium">{{ reservation.room ? reservation.room.room_number : 'N/A' }}</TableCell>
                                <TableCell>{{ formatDate(reservation.check_in_date) }}</TableCell>
                                <TableCell>{{ formatDate(reservation.check_out_date) }}</TableCell>
                                <TableCell>
                                    <Badge :variant="getReservationStatusVariant(reservation.status)">
                                        {{ reservation.status }}
                                    </Badge>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';

defineOptions({ layout: AppLayout });

// Props
const props = defineProps({
    client: {
        type: Object,
        required: true,
    },
    isAdmin: {
        type: Boolean,
        default: false,
    },
    currentUserId: {
        type: Number,
        required: true,
    },
});

// Form setup
const form = useForm({
    name: props.client.name,
    email: props.client.email,
    mobile: props.client.mobile || '',
    country: props.client.country || '',
    gender: props.client.gender || '',
    is_approved: Boolean(props.client.is_approved),
    is_banned: Boolean(props.client.is_banned),
});

// Methods
const updateClient = () => {
    form.put(`/receptionist/clients/${props.client.id}`);
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString();
    } catch (e) {
        return dateString;
    }
};

// Updated to return variant names for Badge component
const getReservationStatusVariant = (status) => {
    const variants = {
        confirmed: 'success',
        checked_in: 'info',
        'checked-in': 'info',
        checked_out: 'secondary',
        'checked-out': 'secondary',
        pending: 'warning',
        cancelled: 'destructive',
    };
    return variants[status] || 'secondary';
};
</script>
