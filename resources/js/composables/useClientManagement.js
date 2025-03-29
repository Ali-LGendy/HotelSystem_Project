import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { useToast } from '@/composables/useToast';

export function useClientManagement() {
  const toast = useToast();
  const showConfirmDialog = ref(false);
  const confirmDialogTitle = ref('');
  const confirmDialogMessage = ref('');
  const confirmAction = ref('');
  const selectedClientId = ref(null);
  const processing = ref(false);

  const openApproveDialog = (client) => {
    selectedClientId.value = client.id;
    confirmDialogTitle.value = 'Approve Client';
    confirmDialogMessage.value = `Are you sure you want to approve ${client.name}? This will allow them to make reservations.`;
    confirmAction.value = 'approve';
    showConfirmDialog.value = true;
  };

  const openRejectDialog = (client) => {
    selectedClientId.value = client.id;
    confirmDialogTitle.value = 'Reject Client';
    confirmDialogMessage.value = `Are you sure you want to reject ${client.name}? This will remove their account from the system.`;
    confirmAction.value = 'reject';
    showConfirmDialog.value = true;
  };

  const cancelConfirmation = () => {
    showConfirmDialog.value = false;
    selectedClientId.value = null;
  };

  const confirmApprove = async () => {
    if (!selectedClientId.value) return;
    
    processing.value = true;
    
    try {
      const response = await axios.post(`/receptionist/api/clients/${selectedClientId.value}/approve`);
      
      if (response.data.success) {
        toast.success('Client approved successfully');
        router.reload({ only: ['pendingClients', 'approvedClientsCount', 'myApprovedClientsCount', 'recentlyApprovedClients'] });
      } else {
        toast.error(response.data.message || 'Failed to approve client');
      }
    } catch (error) {
      console.error('Error approving client:', error);
      toast.error('An error occurred while approving the client');
    } finally {
      processing.value = false;
      showConfirmDialog.value = false;
      selectedClientId.value = null;
    }
  };

  const confirmReject = () => {
    if (!selectedClientId.value) return;
    
    processing.value = true;
    
    router.post(`/receptionist/clients/${selectedClientId.value}/reject`, {}, {
      onSuccess: () => {
        toast.success('Client rejected successfully');
        processing.value = false;
        showConfirmDialog.value = false;
        selectedClientId.value = null;
      },
      onError: (errors) => {
        console.error('Error rejecting client:', errors);
        toast.error('An error occurred while rejecting the client');
        processing.value = false;
        showConfirmDialog.value = false;
        selectedClientId.value = null;
      }
    });
  };

  const banClient = (id) => {
    if (confirm('Are you sure you want to ban this client?')) {
      router.post(`/receptionist/clients/${id}/ban`, {}, {
        onSuccess: () => {
          toast.success('Client banned successfully');
        },
        onError: () => {
          toast.error('Failed to ban client');
        }
      });
    }
  };

  const unbanClient = (id) => {
    if (confirm('Are you sure you want to unban this client?')) {
      router.post(`/receptionist/clients/${id}/unban`, {}, {
        onSuccess: () => {
          toast.success('Client unbanned successfully');
        },
        onError: () => {
          toast.error('Failed to unban client');
        }
      });
    }
  };

  const deleteClient = (id) => {
    if (confirm('Are you sure you want to delete this client? This action cannot be undone.')) {
      router.delete(`/receptionist/clients/${id}`, {
        onSuccess: () => {
          toast.success('Client deleted successfully');
        },
        onError: () => {
          toast.error('Failed to delete client');
        }
      });
    }
  };

  const goToPage = (url) => {
    if (!url) return;
    router.get(url, {}, {
      preserveScroll: true,
      preserveState: true,
      only: ['pendingClients', 'approvedClientsCount', 'myApprovedClientsCount', 'recentlyApprovedClients', 'pendingReservationsForApprovedClients']
    });
  };

  return {
    showConfirmDialog,
    confirmDialogTitle,
    confirmDialogMessage,
    confirmAction,
    selectedClientId,
    processing,
    openApproveDialog,
    openRejectDialog,
    cancelConfirmation,
    confirmApprove,
    confirmReject,
    banClient,
    unbanClient,
    deleteClient,
    goToPage
  };
}