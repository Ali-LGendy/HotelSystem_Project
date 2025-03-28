<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import axios from 'axios';
import { useToast } from '@/composables/useToast';
import { useReservationManagement } from '@/composables/useReservationManagement';
import ReceptionistLayout from '@/layouts/ReceptionistLayout.vue';

// UI Components
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import ConfirmActionDialog from '@/components/receptionist/ConfirmActionDialog.vue';

defineOptions({ layout: ReceptionistLayout });

// Initialize toast
const toast = useToast();

// Props
const props = defineProps({
    reservation: {
        type: Object,
        required: true,
    },
});

// Get reservation management functions
const {
  processing,
  showConfirmDialog,
  confirmDialogTitle,
  confirmDialogMessage,
  confirmAction,
  openApproveDialog,
  openCompleteDialog,
  cancelConfirmation,
  handleConfirmAction
} = useReservationManagement();

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

const goBack = () => {
    router.visit(route('receptionist.reservations.index'), {
        preserveScroll: true,
    });
};

const editReservation = () => {
    router.visit(route('receptionist.reservations.edit', props.reservation.id), {
        preserveScroll: true,
    });
};

// Status update with confirmation
const updateStatus = (newStatus) => {
    if (newStatus === 'confirmed') {
        openApproveDialog(props.reservation);
    } else if (newStatus === 'checked_in') {
        openCompleteDialog(props.reservation);
    } else {
        // For other statuses, use the direct update
        updateStatusDirect(newStatus);
    }
};

// Direct status update without confirmation dialog
const updateStatusDirect = async (newStatus) => {
    // Create a data object with just the status change
    const data = {
        status: newStatus,
        // Include other required fields from the reservation
        room_id: props.reservation.room_id,
        accompany_number: props.reservation.accompany_number,
        check_in_date: props.reservation.check_in_date,
        check_out_date: props.reservation.check_out_date,
        price_paid: props.reservation.price_paid,
        // Add redirect_back parameter to tell the controller to redirect back to this page
        redirect_back: true,
    };

    try {
        // Use axios directly instead of Inertia to handle the JSON response
        const response = await axios.put(`/receptionist/reservations/${props.reservation.id}`, data, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
        });

        // Check if the request was successful
        if (response.data.success) {
            toast.success('Reservation status updated successfully!', {
                duration: 5000 // Show for 5 seconds
            });

            // Reload the page to show the updated status
            window.location.reload();
        } else {
            toast.error('Could not update reservation status. Please try again.');
        }
    } catch (error) {
        console.error('Error updating reservation status:', error);

        // Show more detailed error message if available
        if (error.response && error.response.data && error.response.data.message) {
            toast.error(`Error: ${error.response.data.message}`);
        } else {
            toast.error('Could not update reservation status due to a technical issue. Please try refreshing the page.');
        }
    }
};

const getStatusVariant = (status) => {
    const variants = {
        confirmed: 'success',
        checked_in: 'info',
        'checked-in': 'info', // Support both formats for backward compatibility
        checked_out: 'secondary',
        'checked-out': 'secondary', // Support both formats for backward compatibility
        pending: 'warning',
        cancelled: 'destructive',
    };
    return variants[status] || 'default';
};
</script>

<template>
    <div class="min-h-screen bg-background text-foreground p-8">
        <Card class="mx-auto max-w-4xl">
            <CardHeader>
                <CardTitle class="text-3xl">Reservation Details</CardTitle>
                <CardDescription>
                    Manage reservation information and status
                </CardDescription>
            </CardHeader>

            <CardContent>
                <!-- Details -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-md border border-border bg-card p-4">
                        <h3 class="text-sm font-medium text-muted-foreground">Client Name</h3>
                        <p class="mt-1 text-lg font-semibold">{{ reservation.client.name }}</p>
                    </div>

                    <div class="rounded-md border border-border bg-card p-4">
                        <h3 class="text-sm font-medium text-muted-foreground">Room Number</h3>
                        <p class="mt-1 text-lg font-semibold">{{ reservation.room.room_number }}</p>
                    </div>

                    <div class="rounded-md border border-border bg-card p-4">
                        <h3 class="text-sm font-medium text-muted-foreground">Accompany Number</h3>
                        <p class="mt-1 text-lg font-semibold">{{ reservation.accompany_number }}</p>
                    </div>

                    <div class="rounded-md border border-border bg-card p-4">
                        <h3 class="text-sm font-medium text-muted-foreground">Check-in Date</h3>
                        <p class="mt-1 text-lg font-semibold">{{ formatDate(reservation.check_in_date) }}</p>
                    </div>

                    <div class="rounded-md border border-border bg-card p-4">
                        <h3 class="text-sm font-medium text-muted-foreground">Check-out Date</h3>
                        <p class="mt-1 text-lg font-semibold">{{ formatDate(reservation.check_out_date) }}</p>
                    </div>

                    <div class="rounded-md border border-border bg-card p-4">
                        <h3 class="text-sm font-medium text-muted-foreground">Status</h3>
                        <div class="mt-1">
                            <Badge :variant="getStatusVariant(reservation.status)" class="text-sm font-medium">
                                {{ reservation.status }}
                            </Badge>
                        </div>
                    </div>
                </div>
            </CardContent>

            <CardFooter class="flex justify-end space-x-4">
                <!-- Status Change Buttons -->
                <Button
                    v-if="reservation.status === 'pending'"
                    @click="updateStatus('confirmed')"
                    variant="default"
                    class="bg-green-600 hover:bg-green-700"
                >
                    Approve Reservation
                </Button>

                <Button
                    v-if="reservation.status === 'confirmed'"
                    @click="updateStatus('checked_in')"
                    variant="default"
                    class="bg-green-600 hover:bg-green-700"
                >
                    Check In Guest
                </Button>

                <Button
                    v-if="reservation.status === 'checked_in'"
                    @click="updateStatus('checked_out')"
                    variant="default"
                    class="bg-purple-600 hover:bg-purple-700"
                >
                    Check Out Guest
                </Button>

                <Button
                    @click="editReservation"
                    variant="default"
                    class="bg-yellow-600 hover:bg-yellow-700"
                >
                    Edit Reservation
                </Button>

                <Button
                    @click="goBack"
                    variant="outline"
                >
                    Back to Reservations
                </Button>
            </CardFooter>
        </Card>

        <!-- Confirmation Dialog -->
        <ConfirmActionDialog
            :show="showConfirmDialog"
            :title="confirmDialogTitle"
            :message="confirmDialogMessage"
            :action-type="confirmAction"
            :processing="processing"
            @confirm="handleConfirmAction"
            @cancel="cancelConfirmation"
        />
    </div>
</template>
