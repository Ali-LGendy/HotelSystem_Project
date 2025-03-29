import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { useToast } from '@/composables/useToast';

export function useReservationManagement() {
  const toast = useToast();
  const processing = ref(false);

  // Dialog state
  const showConfirmDialog = ref(false);
  const confirmDialogTitle = ref('');
  const confirmDialogMessage = ref('');
  const confirmAction = ref('');
  const selectedReservationId = ref(null);
  const selectedReservation = ref(null);

  // Open confirmation dialog for approving a reservation
  const openApproveDialog = (reservation) => {
    selectedReservationId.value = typeof reservation === 'object' ? reservation.id : reservation;
    selectedReservation.value = typeof reservation === 'object' ? reservation : null;
    confirmDialogTitle.value = 'Approve Reservation';
    confirmDialogMessage.value = 'Are you sure you want to approve this reservation? This will confirm the booking and notify the client.';
    confirmAction.value = 'approve';
    showConfirmDialog.value = true;
  };

  // Cancel confirmation dialog
  const cancelConfirmation = () => {
    showConfirmDialog.value = false;
    selectedReservationId.value = null;
    selectedReservation.value = null;
  };

  // Approve reservation with confirmation dialog
  const approveReservation = async (reservationId) => {
    // If called directly without a dialog, show the dialog first
    if (!showConfirmDialog.value) {
      openApproveDialog(reservationId);
      return;
    }

    // Use selectedReservationId if reservationId is not provided
    const id = reservationId || selectedReservationId.value;
    if (!id) {
      console.error('No reservation ID provided for approval');
      toast.error('Error: Could not identify reservation');
      return;
    }

    processing.value = true;

    try {
      // Prepare the data for the reservation update
      const data = {
        status: 'confirmed',
        // If we have the full reservation object, use its data
        ...(selectedReservation.value && {
          room_id: selectedReservation.value.room_id,
          accompany_number: selectedReservation.value.accompany_number,
          check_in_date: selectedReservation.value.check_in_date,
          check_out_date: selectedReservation.value.check_out_date,
          price_paid: selectedReservation.value.price_paid,
          client_id: selectedReservation.value.client_id
        })
      };

      // Use axios directly instead of Inertia to handle the JSON response
      const response = await axios.put(`/receptionist/reservations/${id}`, data, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      });

      // Check if the request was successful
      if (response.data.success) {
        const clientName = selectedReservation.value && selectedReservation.value.client ?
          selectedReservation.value.client.name : 'Client';

        toast.success('Reservation Approved Successfully!', {
          description: `${clientName}'s reservation has been confirmed and they have been notified.`,
          duration: 6000, // Show for 6 seconds
          position: 'top-center'
        });

        // Use window.location.reload() to refresh the page completely
        // This avoids Inertia-related issues with the JSON response
        window.location.reload();
      } else {
        toast.error('Failed to Approve Reservation', {
          description: response.data.message || 'Please try again or contact support if the issue persists.',
          duration: 6000
        });
      }
    } catch (error) {
      console.error('Error in approveReservation function:', error);

      let errorMessage = 'An error occurred while approving the reservation';
      if (error.response && error.response.data && error.response.data.message) {
        errorMessage = error.response.data.message;
      }

      toast.error('Approval Failed', {
        description: errorMessage,
        duration: 6000
      });
    } finally {
      processing.value = false;
      showConfirmDialog.value = false;
      selectedReservationId.value = null;
      selectedReservation.value = null;
    }
  };

  // Open confirmation dialog for cancelling a reservation
  const openCancelDialog = (reservation) => {
    selectedReservationId.value = typeof reservation === 'object' ? reservation.id : reservation;
    selectedReservation.value = typeof reservation === 'object' ? reservation : null;
    confirmDialogTitle.value = 'Cancel Reservation';
    confirmDialogMessage.value = 'Are you sure you want to cancel this reservation? This action cannot be undone.';
    confirmAction.value = 'cancel';
    showConfirmDialog.value = true;
  };

  // Cancel reservation with confirmation dialog
  const cancelReservation = async (reservationId) => {
    // If called directly without a dialog, show the dialog first
    if (!showConfirmDialog.value) {
      openCancelDialog(reservationId);
      return;
    }

    // Use selectedReservationId if reservationId is not provided
    const id = reservationId || selectedReservationId.value;
    if (!id) {
      console.error('No reservation ID provided for cancellation');
      toast.error('Error: Could not identify reservation');
      return;
    }

    processing.value = true;

    try {
      // Use axios directly instead of Inertia to handle the JSON response
      const response = await axios.post(`/receptionist/reservations/${id}/cancel`, {}, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      });

      // Check if the request was successful
      if (response.data.success) {
        toast.success('Reservation cancelled successfully');
        // Use window.location.reload() to refresh the page completely
        window.location.reload();
      } else {
        toast.error('Failed to Cancel Reservation', {
          description: response.data.message || 'Please try again or contact support if the issue persists.',
          duration: 6000
        });
      }
    } catch (error) {
      console.error('Error in cancelReservation function:', error);

      let errorMessage = 'An error occurred while cancelling the reservation';
      if (error.response && error.response.data && error.response.data.message) {
        errorMessage = error.response.data.message;
      }

      toast.error('Cancellation Failed', {
        description: errorMessage,
        duration: 6000
      });
    } finally {
      processing.value = false;
      showConfirmDialog.value = false;
      selectedReservationId.value = null;
      selectedReservation.value = null;
    }
  };

  // Open confirmation dialog for completing a reservation
  const openCompleteDialog = (reservation) => {
    selectedReservationId.value = typeof reservation === 'object' ? reservation.id : reservation;
    selectedReservation.value = typeof reservation === 'object' ? reservation : null;
    confirmDialogTitle.value = 'Complete Reservation';
    confirmDialogMessage.value = 'Are you sure you want to mark this reservation as completed?';
    confirmAction.value = 'complete';
    showConfirmDialog.value = true;
  };

  // Complete reservation with confirmation dialog
  const completeReservation = async (reservationId) => {
    // If called directly without a dialog, show the dialog first
    if (!showConfirmDialog.value) {
      openCompleteDialog(reservationId);
      return;
    }

    // Use selectedReservationId if reservationId is not provided
    const id = reservationId || selectedReservationId.value;
    if (!id) {
      console.error('No reservation ID provided for completion');
      toast.error('Error: Could not identify reservation');
      return;
    }

    processing.value = true;

    try {
      // Use axios directly instead of Inertia to handle the JSON response
      const response = await axios.post(`/receptionist/reservations/${id}/complete`, {}, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      });

      // Check if the request was successful
      if (response.data.success) {
        toast.success('Reservation marked as completed');
        // Use window.location.reload() to refresh the page completely
        window.location.reload();
      } else {
        toast.error('Failed to Complete Reservation', {
          description: response.data.message || 'Please try again or contact support if the issue persists.',
          duration: 6000
        });
      }
    } catch (error) {
      console.error('Error in completeReservation function:', error);

      let errorMessage = 'An error occurred while completing the reservation';
      if (error.response && error.response.data && error.response.data.message) {
        errorMessage = error.response.data.message;
      }

      toast.error('Completion Failed', {
        description: errorMessage,
        duration: 6000
      });
    } finally {
      processing.value = false;
      showConfirmDialog.value = false;
      selectedReservationId.value = null;
      selectedReservation.value = null;
    }
  };

  const goToPage = (url) => {
    if (!url) return;
    router.get(url, {}, {
      preserveScroll: true,
      preserveState: true,
      only: ['clientsReservations', 'pendingReservations', 'reservations']
    });
  };

  const viewReservationDetails = (reservationId) => {
    router.visit(`/receptionist/reservations/${reservationId}`, {
      preserveScroll: false
    });
  };

  // Handle confirmation based on action type
  const handleConfirmAction = () => {
    switch (confirmAction.value) {
      case 'approve':
        // Pass the selectedReservationId to approveReservation
        approveReservation(selectedReservationId.value);
        break;
      case 'cancel':
        // Pass the selectedReservationId to cancelReservation
        cancelReservation(selectedReservationId.value);
        break;
      case 'complete':
        // Pass the selectedReservationId to completeReservation
        completeReservation(selectedReservationId.value);
        break;
    }
  };

  return {
    processing,
    // Dialog state
    showConfirmDialog,
    confirmDialogTitle,
    confirmDialogMessage,
    confirmAction,
    selectedReservationId,
    selectedReservation,
    // Dialog actions
    openApproveDialog,
    openCancelDialog,
    openCompleteDialog,
    cancelConfirmation,
    handleConfirmAction,
    // Reservation actions
    approveReservation,
    cancelReservation,
    completeReservation,
    goToPage,
    viewReservationDetails
  };
}