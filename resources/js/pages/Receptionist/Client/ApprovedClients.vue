<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';
import ApprovalDataTable from './ApprovalDataTable.vue';
import { useToast } from '@/composables/useToast';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';

defineOptions({ layout: AppLayout });

// Initialize toast
const toast = useToast();

// Props
const props = defineProps({
    pendingClients: {
        type: Object,
        required: true,
    },
    approvedClientsCount: {
        type: Number,
        default: 0,
    },
    myApprovedClientsCount: {
        type: Number,
        default: 0,
    },
    recentlyApprovedClients: {
        type: Array,
        default: () => [],
    },
    pendingReservationsForApprovedClients: {
        type: Array,
        default: () => [],
    },
    currentUserId: {
        type: Number,
        default: null,
    },
    userRole: {
        type: String,
        default: 'receptionist',
    },
    isAdmin: {
        type: Boolean,
        default: false,
    },
});

// State
const showConfirmDialog = ref(false);
const confirmDialogTitle = ref('');
const confirmDialogMessage = ref('');
const confirmAction = ref('');
const selectedClientId = ref(null);
const currentSort = ref({
    field: 'created_at',
    direction: 'desc',
});
const perPage = ref(10);

// Computed properties for ApprovalDataTable
const approvalStats = computed(() => ({
    totalClients: props.pendingClients.total + props.approvedClientsCount,
    approvedClients: props.approvedClientsCount,
    pendingClients: props.pendingClients.total,
}));

const recentApprovals = computed(() => props.recentlyApprovedClients);
const pendingReservations = computed(() => props.pendingReservationsForApprovedClients);

// Methods
const goToPage = (url) => {
    if (!url) return;
    router.get(url, {}, {
        preserveScroll: true,
        preserveState: true,
        only: ['pendingClients', 'approvedClientsCount', 'myApprovedClientsCount', 'recentlyApprovedClients', 'pendingReservationsForApprovedClients']
    });
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

// Enhanced pagination with sorting
const sortAndPaginate = (page = 1, perPage = 10, sortBy = 'created_at', sortDir = 'desc') => {
    // Update current sort state
    currentSort.value = {
        field: sortBy,
        direction: sortDir,
    };

    // Navigate with new parameters using Inertia's get method
    router.get(window.location.pathname, {
        page,
        per_page: perPage,
        sort_by: sortBy,
        sort_dir: sortDir
    }, {
        preserveScroll: true,
        preserveState: true, // Keep component state between requests
        only: ['pendingClients', 'approvedClientsCount', 'myApprovedClientsCount', 'recentlyApprovedClients', 'pendingReservationsForApprovedClients'], // Only refresh these data props
        replace: true // Replace current history entry instead of adding a new one
    });
};

const approveClient = (clientId) => {
    // Find the client in the pending clients list
    const client = props.pendingClients.data.find(c => c.id === clientId);

    selectedClientId.value = clientId;
    confirmAction.value = 'approve';
    confirmDialogTitle.value = 'Confirm Client Approval';
    confirmDialogMessage.value = `Are you sure you want to approve ${client ? client.name : 'this client'}? They will be added to your approved clients list.`;
    showConfirmDialog.value = true;
};

const rejectClient = (clientId) => {
    // Find the client in the pending clients list
    const client = props.pendingClients.data.find(c => c.id === clientId);

    selectedClientId.value = clientId;
    confirmAction.value = 'reject';
    confirmDialogTitle.value = 'Confirm Client Rejection';
    confirmDialogMessage.value = `Are you sure you want to reject ${client ? client.name : 'this client'}? This action cannot be undone.`;
    showConfirmDialog.value = true;
};

const cancelConfirmation = () => {
    showConfirmDialog.value = false;
    selectedClientId.value = null;
};

const confirmApprove = () => {
    if (!selectedClientId.value) return;

    // Find the client in the pending clients list for the toast message
    const client = props.pendingClients.data.find(c => c.id === selectedClientId.value);
    const clientName = client ? client.name : 'Client';

    // Hide the confirmation dialog
    showConfirmDialog.value = false;

    // Use Inertia router.post directly
    router.post(route('receptionist.clients.approve', selectedClientId.value), {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Show success toast notification with client name
            toast.success(`${clientName} has been approved successfully!`);
        },
        onError: (errors) => {
            // Show error toast notification
            toast.error(`Failed to approve client: ${Object.values(errors).flat().join(', ')}`);
        }
    });
};

const confirmReject = () => {
    if (!selectedClientId.value) return;

    // Find the client in the pending clients list for the toast message
    const client = props.pendingClients.data.find(c => c.id === selectedClientId.value);
    const clientName = client ? client.name : 'Client';

    // Hide the confirmation dialog
    showConfirmDialog.value = false;

    // Use Inertia router.post directly
    router.post(route('receptionist.clients.reject', selectedClientId.value), {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Show success toast notification with client name
            toast.success(`${clientName} has been rejected.`);
        },
        onError: (errors) => {
            // Show error toast notification
            toast.error(`Failed to reject client: ${Object.values(errors).flat().join(', ')}`);
        }
    });
};
</script>

<template>
    <div class="min-h-screen bg-background text-foreground p-8">
        <!-- Navigation Header -->
        <div class="rounded-lg border border-border bg-card p-8 shadow-sm mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Client Management</h1>
                    <p class="text-muted-foreground">Manage client approvals and reservations</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <Button
                        @click="navigateTo('/receptionist/clients/my-approved')"
                        variant="outline"
                    >
                        My Approved Clients
                    </Button>
                    <Button
                        @click="navigateTo('/receptionist/clients/reservations')"
                        variant="default"
                    >
                        All My Clients Reservations
                    </Button>
                    <Button
                        @click="navigateTo('/receptionist/reservations')"
                        variant="outline"
                    >
                        Pending Reservations
                    </Button>
                    <Button
                        v-if="isAdmin"
                        @click="navigateTo('/receptionist/clients/all')"
                        variant="default"
                    >
                        All Clients
                    </Button>
                    <Button
                        v-if="isAdmin"
                        @click="navigateTo('/receptionist/clients/create')"
                        variant="default"
                        class="bg-green-600 hover:bg-green-700"
                    >
                        Add Client
                    </Button>
                </div>
            </div>
        </div>

        <!-- Pending Clients Section -->
        <div class="mb-8">
            <h2 class="mb-4 text-2xl font-bold">Pending Client Registrations</h2>

            <div v-if="pendingClients.data.length === 0" class="text-center text-muted-foreground py-8 border rounded-lg">
                <p class="text-lg">No pending client registrations found.</p>
            </div>

            <div v-else>
                <Table class="w-full overflow-hidden rounded-lg border border-border">
                    <TableHeader>
                        <TableRow>
                            <TableHead
                                @click="sortAndPaginate(1, perPage, 'name', currentSort.field === 'name' && currentSort.direction === 'asc' ? 'desc' : 'asc')"
                                class="cursor-pointer hover:bg-accent/10"
                            >
                                Client Name
                                <span v-if="currentSort.field === 'name'" class="ml-1">
                                    {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                                </span>
                            </TableHead>
                            <TableHead
                                @click="sortAndPaginate(1, perPage, 'email', currentSort.field === 'email' && currentSort.direction === 'asc' ? 'desc' : 'asc')"
                                class="cursor-pointer hover:bg-accent/10"
                            >
                                Email
                                <span v-if="currentSort.field === 'email'" class="ml-1">
                                    {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                                </span>
                            </TableHead>
                            <TableHead
                                @click="sortAndPaginate(1, perPage, 'mobile', currentSort.field === 'mobile' && currentSort.direction === 'asc' ? 'desc' : 'asc')"
                                class="cursor-pointer hover:bg-accent/10"
                            >
                                Mobile
                                <span v-if="currentSort.field === 'mobile'" class="ml-1">
                                    {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                                </span>
                            </TableHead>
                            <TableHead
                                @click="sortAndPaginate(1, perPage, 'country', currentSort.field === 'country' && currentSort.direction === 'asc' ? 'desc' : 'asc')"
                                class="cursor-pointer hover:bg-accent/10"
                            >
                                Country
                                <span v-if="currentSort.field === 'country'" class="ml-1">
                                    {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                                </span>
                            </TableHead>
                            <TableHead
                                @click="sortAndPaginate(1, perPage, 'gender', currentSort.field === 'gender' && currentSort.direction === 'asc' ? 'desc' : 'asc')"
                                class="cursor-pointer hover:bg-accent/10"
                            >
                                Gender
                                <span v-if="currentSort.field === 'gender'" class="ml-1">
                                    {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                                </span>
                            </TableHead>
                            <TableHead>Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="client in pendingClients.data"
                            :key="client.id"
                            class="transition hover:bg-accent/10"
                        >
                            <TableCell>{{ client.name }}</TableCell>
                            <TableCell>{{ client.email }}</TableCell>
                            <TableCell>{{ client.mobile || 'N/A' }}</TableCell>
                            <TableCell>{{ client.country || 'N/A' }}</TableCell>
                            <TableCell>{{ client.gender || 'N/A' }}</TableCell>
                            <TableCell>
                                <div class="flex gap-2">
                                    <Button
                                        @click="approveClient(client.id)"
                                        variant="default"
                                        size="sm"
                                        class="bg-green-600 hover:bg-green-700"
                                    >
                                        Approve
                                    </Button>
                                    <Button
                                        @click="rejectClient(client.id)"
                                        variant="destructive"
                                        size="sm"
                                    >
                                        Reject
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <!-- Enhanced Pagination for Pending Clients -->
                <div v-if="pendingClients.data.length > 0" class="mt-4 flex items-center justify-between">
                    <div class="text-sm text-muted-foreground">
                        Showing {{ pendingClients.from }} to {{ pendingClients.to }} of {{ pendingClients.total }} pending clients
                    </div>

                    <!-- Page Size Selector and Navigation -->
                    <div class="flex items-center gap-4">
                        <div class="flex items-center">
                            <span class="mr-2 text-sm text-muted-foreground">Per page:</span>
                            <select
                                v-model="perPage"
                                @change="sortAndPaginate(1, perPage, currentSort.field, currentSort.direction)"
                                class="rounded-md bg-card border border-border px-2 py-1 text-sm"
                            >
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </div>

                        <!-- Page Navigation -->
                        <div class="flex gap-2">
                            <Button
                                v-for="page in pendingClients.links"
                                :key="page.label"
                                @click="
                                    page.url &&
                                    sortAndPaginate(
                                        page.label === '&laquo; Previous'
                                            ? pendingClients.current_page - 1
                                            : page.label === 'Next &raquo;'
                                              ? pendingClients.current_page + 1
                                              : parseInt(page.label),
                                        perPage,
                                        currentSort.field,
                                        currentSort.direction,
                                    )
                                "
                                :disabled="!page.url"
                                :variant="page.active ? 'default' : 'outline'"
                                size="sm"
                                v-html="page.label"
                            ></Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approval Data Tables -->
        <ApprovalDataTable
            :stats="approvalStats"
            :recent-approvals="recentApprovals"
            :pending-reservations="pendingReservations"
        />

        <!-- Confirmation Dialog -->
        <Dialog :open="showConfirmDialog" @update:open="showConfirmDialog = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ confirmDialogTitle }}</DialogTitle>
                    <DialogDescription>
                        {{ confirmDialogMessage }}
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="cancelConfirmation">
                        Cancel
                    </Button>
                    <Button
                        :variant="confirmAction === 'approve' ? 'default' : 'destructive'"
                        @click="confirmAction === 'approve' ? confirmApprove() : confirmReject()"
                    >
                        {{ confirmAction === 'approve' ? 'Approve' : 'Reject' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
