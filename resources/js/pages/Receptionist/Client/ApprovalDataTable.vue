<script setup>
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { useToast } from '@/composables/useToast';
import axios from 'axios';

defineOptions({ layout: AppLayout });

// Props
const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            totalClients: 0,
            approvedClients: 0,
            pendingClients: 0,
        }),
    },
    recentApprovals: {
        type: Array,
        default: () => [],
    },
    pendingReservations: {
        type: Array,
        default: () => [],
    },
});

// Initialize toast
const toast = useToast();

// For debugging
console.log('ApprovalDataTable props:', {
    stats: props.stats,
    recentApprovals: props.recentApprovals,
    pendingReservations: props.pendingReservations,
});

// Methods
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString();
    } catch (e) {
        return dateString;
    }
};

// Navigation method using Inertia
const navigateTo = (url) => {
    router.visit(url, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
        onSuccess: () => {
            console.log('Navigation successful to:', url);
        },
        onError: (errors) => {
            console.error('Navigation error:', errors);
        }
    });
};

const approveReservation = async (reservation) => {
    if (confirm('Are you sure you want to approve this reservation?')) {
        try {
            // Prepare the data to send
            const data = {
                status: 'confirmed',
                room_id: reservation.room_id,
                client_id: reservation.client_id,
                accompany_number: reservation.accompany_number,
                price_paid: reservation.price_paid,
                check_in_date: reservation.check_in_date,
                check_out_date: reservation.check_out_date
            };

            // Use axios to make the request with proper method
            const response = await axios.put(`/receptionist/reservations/${reservation.id}`, data);
            console.log('Reservation approval response:', response.data);

            // Show success message using toast instead of alert
            toast.success('Reservation approved successfully!');

            // Reload the current page
            router.visit(window.location.pathname, {
                method: 'get',
                preserveScroll: false,
                preserveState: false,
                replace: true,
                onSuccess: () => {
                    console.log('Page reloaded after reservation approval');
                },
            });
        } catch (error) {
            console.error('Error approving reservation:', error.response?.data || error);
            toast.error('Could not approve reservation due to a technical issue. Please try refreshing the page.');
        }
    }
};
</script>

<template>
    <div class="min-h-screen bg-background text-foreground p-8">
        <h1 class="mb-8 text-3xl font-bold">Approval Data</h1>

        <!-- Approval Status -->
        <div class="mb-6 rounded-lg border border-border bg-accent/10 p-6">
            <h2 class="mb-4 text-xl font-semibold">Approval Status</h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-lg border border-border bg-card p-4 shadow-sm">
                    <div class="text-sm text-muted-foreground">Total Clients</div>
                    <div class="text-2xl font-bold">{{ stats.totalClients }}</div>
                </div>
                <div class="rounded-lg border border-green-600/20 bg-green-950/10 p-4 shadow-sm">
                    <div class="text-sm text-muted-foreground">Approved Clients</div>
                    <div class="text-2xl font-bold">{{ stats.approvedClients }}</div>
                </div>
                <div class="rounded-lg border border-yellow-600/20 bg-yellow-950/10 p-4 shadow-sm">
                    <div class="text-sm text-muted-foreground">Pending Clients</div>
                    <div class="text-2xl font-bold">{{ stats.pendingClients }}</div>
                </div>
            </div>
        </div>

        <!-- Recent Approvals -->
        <div class="mb-6">
            <h2 class="mb-4 text-xl font-semibold">Recent Approvals</h2>
            <div v-if="recentApprovals.length === 0" class="text-center text-muted-foreground py-8 border rounded-lg">
                <p>No recent approvals found.</p>
            </div>
            <div v-else>
                <Table class="w-full overflow-hidden rounded-lg border border-border">
                    <TableHeader>
                        <TableRow>
                            <TableHead>Client Name</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Approved On</TableHead>
                            <TableHead>Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="client in recentApprovals"
                            :key="client.id"
                            class="transition hover:bg-accent/10"
                        >
                            <TableCell>{{ client.name }}</TableCell>
                            <TableCell>{{ client.email }}</TableCell>
                            <TableCell>{{ formatDate(client.approved_at) }}</TableCell>
                            <TableCell>
                                <div class="flex gap-2">
                                    <Button
                                        @click="navigateTo(`/receptionist/clients/${client.id}/reservations`)"
                                        variant="default"
                                    >
                                        View Reservations
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>

        <!-- Pending Reservations for Approved Clients -->
        <div>
            <h2 class="mb-4 text-xl font-semibold">Pending Reservations for Approved Clients</h2>
            <div v-if="pendingReservations.length === 0" class="text-center text-muted-foreground py-8 border rounded-lg">
                <p>No pending reservations found for your approved clients.</p>
            </div>
            <div v-else>
                <Table class="w-full overflow-hidden rounded-lg border border-border">
                    <TableHeader>
                        <TableRow>
                            <TableHead>Client Name</TableHead>
                            <TableHead>Room Number</TableHead>
                            <TableHead>Check-in Date</TableHead>
                            <TableHead>Check-out Date</TableHead>
                            <TableHead>Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="reservation in pendingReservations"
                            :key="reservation.id"
                            class="transition hover:bg-accent/10"
                        >
                            <TableCell>
                                {{ reservation.client ? reservation.client.name : 'N/A' }}
                            </TableCell>
                            <TableCell>
                                {{ reservation.room ? reservation.room.room_number : 'N/A' }}
                            </TableCell>
                            <TableCell>{{ formatDate(reservation.check_in_date) }}</TableCell>
                            <TableCell>{{ formatDate(reservation.check_out_date) }}</TableCell>
                            <TableCell>
                                <div class="flex gap-2">
                                    <Button
                                        @click="approveReservation(reservation)"
                                        variant="default"
                                    >
                                        Approve
                                    </Button>
                                    <Button
                                        @click="navigateTo(`/receptionist/reservations/${reservation.id}`)"
                                        variant="outline"
                                    >
                                        View
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </div>
</template>
