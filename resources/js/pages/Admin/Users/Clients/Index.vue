<script setup>
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    unapprovedClients: {
        type: Object,
        default: () => ({ data: [] }),
    },
    approvedClients: {
        type: Object,
        default: () => ({ data: [] }),
    },
    allClients: {
        type: Object,
        default: () => ({ data: [] }),
    },
    isAdmin: {
        type: Boolean,
        default: false,
    },
    isReceptionist: {
        type: Boolean,
        default: false,
    }
});

const currentView = ref('unapproved');

// Determine which clients to display based on user role and current view
const displayClients = computed(() => {
    if (props.isReceptionist) {
        return currentView.value === 'unapproved' 
            ? props.unapprovedClients.data 
            : props.approvedClients.data;
    }
    return props.allClients.data;
});

// Determine if actions column should be shown
const showActionsColumn = computed(() => {
    // Show actions for:
    // 1. All non-receptionist users
    // 2. Receptionist viewing unapproved clients
    return !props.isReceptionist || currentView.value === 'unapproved';
});

defineOptions({ layout: AppLayout });

// Initialize toast
const toast = useToast();

// Function to approve a client
const approveClient = (client) => {
    router.patch(route('admin.users.clients.approve', client.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`Client ${client.name} approved successfully!`);
        },
        onError: (errors) => {
            toast.error(`Failed to approve client: ${Object.values(errors).flat().join(', ')}`);
        }
    });
};
</script>

<template>
    <div class="min-h-screen bg-background text-foreground p-8">
        <h1 class="mb-8 text-3xl font-bold">Manage Clients</h1>

        <!-- Receptionist Buttons -->
        <div v-if="isReceptionist" class="mb-6 flex gap-4">
            <Button 
                @click="currentView = 'unapproved'" 
                :variant="currentView === 'unapproved' ? 'default' : 'outline'"
            >
                Unapproved Clients
            </Button>
            <Button 
                @click="currentView = 'approved'" 
                :variant="currentView === 'approved' ? 'default' : 'outline'"
            >
                My Approved Clients
            </Button>
        </div>

        <Table 
            v-if="displayClients.length > 0" 
            class="w-full overflow-hidden rounded-lg border border-border"
        >
            <TableHeader>
                <TableRow>
                    <TableHead>Name</TableHead>
                    <TableHead>Email</TableHead>
                    <TableHead>Gender</TableHead>
                    <TableHead>Country</TableHead>
                    <TableHead>Avatar Image</TableHead>
                    
                    <!-- Conditionally render Approved By column for non-receptionist users -->
                    <TableHead v-if="!isReceptionist">Approved By</TableHead>
                    
                    <!-- Conditionally render Actions column -->
                    <TableHead v-if="showActionsColumn">Actions</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <TableRow 
                    v-for="client in displayClients" 
                    :key="client.id" 
                    class="transition hover:bg-accent/10"
                >
                    <TableCell>{{ client.name }}</TableCell>
                    <TableCell>{{ client.email }}</TableCell>
                    <TableCell>{{ client.gender }}</TableCell>
                    <TableCell>{{ client.country }}</TableCell>
                    <TableCell>
                        <img 
                            v-if="client.avatar_img" 
                            :src="`/storage/${client.avatar_img}`" 
                            alt="Avatar" 
                            class="h-10 w-10 rounded-full border border-border"
                        />
                        <span v-else class="text-muted-foreground">No Avatar</span>
                    </TableCell>
                    
                    <!-- Approved By column for non-receptionist users -->
                    <TableCell v-if="!isReceptionist">
                        {{ client.manager ? client.manager.name : 'Not Approved' }}
                    </TableCell>
                    
                    <!-- Conditionally render Actions cell -->
                    <TableCell v-if="showActionsColumn">
                        <div class="flex gap-4">
                            <!-- Approve Button for Non-Receptionists -->
                            <Button 
                                v-if="!isReceptionist"
                                @click="approveClient(client)"
                                :disabled="client.is_approved"
                                variant="default"
                            >
                                {{ client.is_approved ? 'Approved' : 'Approve' }}
                            </Button>

                            <!-- Approve Button for Unapproved Clients (Receptionists) -->
                            <Button 
                                v-if="!client.is_approved && isReceptionist" 
                                @click="approveClient(client)"
                                variant="default"
                            >
                                Approve
                            </Button>

                            <!-- Edit and Delete Buttons for Admin -->
                            <template v-if="isAdmin">
                                <Link :href="route('admin.users.clients.edit', client)">
                                    <Button variant="outline">Edit</Button>
                                </Link>
                                <Link
                                    method="delete"
                                    :href="route('admin.users.clients.destroy', client)"
                                    as="button"
                                >
                                    <Button variant="destructive">Delete</Button>
                                </Link>
                            </template>
                        </div>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>

        <!-- Empty State -->
        <div 
            v-else 
            class="text-center text-muted-foreground py-8 border rounded-lg"
        >
            {{ 
                isReceptionist 
                    ? (currentView === 'unapproved' 
                        ? 'No Unapproved Clients' 
                        : 'No Approved Clients') 
                    : 'No Clients Found' 
            }}
        </div>
    </div>
</template>