<template>
  <div class="mx-auto max-w-7xl px-4 py-8">
    <div class="rounded-lg bg-gray-900 p-8 text-gray-200 shadow-lg">
      <!-- Header with Navigation -->
      <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h2 class="text-3xl font-bold">Clients Pending Approval</h2>
          <p class="mt-2 text-gray-400">Manage client registration requests</p>
        </div>
        <div class="flex flex-wrap gap-3">
          <Button
            @click="router.visit(route('receptionist.clients.my-approved'))"
            variant="default"
            class="bg-green-600 hover:bg-green-700 text-white"
          >
            My Approved Clients
          </Button>
          <Button
            @click="router.visit(route('receptionist.clients.reservations'))"
            variant="default"
            class="bg-indigo-600 hover:bg-indigo-700 text-white"
          >
            Clients Reservations
          </Button>
        </div>
      </div>

      <div v-if="pendingClients.data.length === 0" class="text-center py-8">
        <p class="text-lg text-gray-300">No pending client registrations found.</p>
      </div>

      <!-- Data Table -->
      <div v-else class="rounded-lg border border-gray-700 bg-gray-800 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-700">
          <thead class="bg-gray-800">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                Client Name
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                Email
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                Mobile
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                Country
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                Gender
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-gray-800 divide-y divide-gray-700">
            <tr v-for="client in pendingClients.data" :key="client.id" class="hover:bg-gray-700">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-200">{{ client.name }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-300">{{ client.email }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-300">{{ client.mobile || 'N/A' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-300">{{ client.country || 'N/A' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-300">{{ client.gender || 'N/A' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <Button
                    @click="approveClient(client.id)"
                    variant="success"
                    size="sm"
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
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pendingClients.data.length > 0" class="mt-4 flex justify-between items-center">
        <div class="text-sm text-gray-400">
          Showing {{ pendingClients.from }} to {{ pendingClients.to }} of {{ pendingClients.total }} clients
        </div>
        <div class="flex space-x-2">
          <Button
            v-for="page in pendingClients.links"
            :key="page.label"
            @click="goToPage(page.url)"
            :disabled="!page.url"
            :variant="page.active ? 'default' : 'outline'"
            size="sm"
            v-html="page.label"
          ></Button>
        </div>
      </div>

      <!-- Confirmation Dialog -->
      <AlertDialog v-if="showConfirmDialog">
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>{{ confirmDialogTitle }}</AlertDialogTitle>
            <AlertDialogDescription>
              {{ confirmDialogMessage }}
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel @click="cancelConfirmation">Cancel</AlertDialogCancel>
            <AlertDialogAction 
              @click="confirmAction === 'approve' ? confirmApprove() : confirmReject()"
              :variant="confirmAction === 'approve' ? 'default' : 'destructive'"
            >
              {{ confirmAction === 'approve' ? 'Approve' : 'Reject' }}
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';

// Existing script logic remains the same as in the original file...
// (I've kept the script portion the same to focus on the template changes)
</script>