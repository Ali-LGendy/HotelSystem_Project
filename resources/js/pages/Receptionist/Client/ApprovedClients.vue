<template>
  <div class="mx-auto max-w-7xl px-4 py-8">
    <div class="rounded-lg bg-gray-900 p-8 text-gray-200 shadow-lg">
      <!-- Header with Navigation -->
      <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h2 class="text-3xl font-bold">Pending Client Approvals</h2>
          <p class="mt-2 text-gray-400">Review and approve new client registrations</p>
        </div>
        <div class="flex flex-wrap gap-3">
          <button
            @click="navigateTo('/receptionist/clients/my-approved')"
            class="inline-flex items-center justify-center rounded-md bg-secondary px-4 py-2 text-sm font-medium text-secondary-foreground shadow-sm transition-colors hover:bg-secondary/80 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
          >
            My Approved Clients
          </button>
          <button
            @click="navigateTo('/receptionist/clients/reservations')"
            class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
          >
            All My Clients Reservations
          </button>
          <button
            @click="navigateTo('/receptionist/reservations')"
            class="inline-flex items-center justify-center rounded-md bg-secondary px-4 py-2 text-sm font-medium text-secondary-foreground shadow-sm transition-colors hover:bg-secondary/80 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
          >
            Pending Reservations
          </button>
          <!-- Only show All Clients button to admin -->
          <button
            v-if="isAdmin"
            @click="navigateTo('/receptionist/clients/all')"
            class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
          >
            All Clients
          </button>
        </div>
      </div>

      <!-- Pending Clients Section -->
      <div class="mb-8">
        <h3 class="text-2xl font-bold mb-4 text-gray-100">Pending Client Registrations</h3>

        <div v-if="pendingClients.data.length === 0" class="text-center py-8">
          <p class="text-lg text-gray-300">No pending client registrations found.</p>
        </div>

        <div v-else class="rounded-lg border border-gray-700 bg-gray-800 overflow-hidden">
          <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-800">
              <tr>
                <th
                  @click="sortAndPaginate(1, perPage, 'name', currentSort.field === 'name' && currentSort.direction === 'asc' ? 'desc' : 'asc')"
                  scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-700"
                >
                  Client Name
                  <span v-if="currentSort.field === 'name'" class="ml-1">
                    {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                  </span>
                </th>
                <th
                  @click="sortAndPaginate(1, perPage, 'email', currentSort.field === 'email' && currentSort.direction === 'asc' ? 'desc' : 'asc')"
                  scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-700"
                >
                  Email
                  <span v-if="currentSort.field === 'email'" class="ml-1">
                    {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                  </span>
                </th>
                <th
                  @click="sortAndPaginate(1, perPage, 'mobile', currentSort.field === 'mobile' && currentSort.direction === 'asc' ? 'desc' : 'asc')"
                  scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-700"
                >
                  Mobile
                  <span v-if="currentSort.field === 'mobile'" class="ml-1">
                    {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                  </span>
                </th>
                <th
                  @click="sortAndPaginate(1, perPage, 'country', currentSort.field === 'country' && currentSort.direction === 'asc' ? 'desc' : 'asc')"
                  scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-700"
                >
                  Country
                  <span v-if="currentSort.field === 'country'" class="ml-1">
                    {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                  </span>
                </th>
                <th
                  @click="sortAndPaginate(1, perPage, 'gender', currentSort.field === 'gender' && currentSort.direction === 'asc' ? 'desc' : 'asc')"
                  scope="col"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-700"
                >
                  Gender
                  <span v-if="currentSort.field === 'gender'" class="ml-1">
                    {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                  </span>
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
                    <button
                      @click="approveClient(client.id)"
                      class="rounded-md bg-green-700 px-3 py-1 text-sm font-medium text-white hover:bg-green-600"
                    >
                        My Approved Clients
                    </a>
                    <a
                        href="/receptionist/clients/reservations"
                        class="rounded-lg bg-purple-600 px-4 py-2 font-semibold text-white transition hover:bg-purple-700"
                    >
                        Clients Reservations
                    </a>
                    <a
                        href="/receptionist/reservations"
                        class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white transition hover:bg-indigo-700"
                    >
                        Pending Reservations
                    </a>
                    <!-- Only show All Clients button to admin -->
                    <a
                        v-if="isAdmin"
                        href="/receptionist/clients/all"
                        class="rounded-lg bg-green-600 px-4 py-2 font-semibold text-white transition hover:bg-green-700"
                    >
                        All Clients
                    </a>
                </div>
            </div>

            <!-- Pending Clients Section -->
            <div class="mb-8">
                <h3 class="mb-4 text-2xl font-bold text-gray-100">Pending Client Registrations</h3>

                <div v-if="pendingClients.data.length === 0" class="py-8 text-center">
                    <p class="text-lg text-gray-300">No pending client registrations found.</p>
                </div>

                <div v-else class="overflow-hidden rounded-lg border border-gray-700 bg-gray-800">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead class="bg-gray-800">
                            <tr>
                                <th
                                    @click="
                                        sortAndPaginate(
                                            1,
                                            perPage,
                                            'name',
                                            currentSort.field === 'name' && currentSort.direction === 'asc' ? 'desc' : 'asc',
                                        )
                                    "
                                    scope="col"
                                    class="cursor-pointer px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300 hover:bg-gray-700"
                                >
                                    Client Name
                                    <span v-if="currentSort.field === 'name'" class="ml-1">
                                        {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </th>
                                <th
                                    @click="
                                        sortAndPaginate(
                                            1,
                                            perPage,
                                            'email',
                                            currentSort.field === 'email' && currentSort.direction === 'asc' ? 'desc' : 'asc',
                                        )
                                    "
                                    scope="col"
                                    class="cursor-pointer px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300 hover:bg-gray-700"
                                >
                                    Email
                                    <span v-if="currentSort.field === 'email'" class="ml-1">
                                        {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </th>
                                <th
                                    @click="
                                        sortAndPaginate(
                                            1,
                                            perPage,
                                            'mobile',
                                            currentSort.field === 'mobile' && currentSort.direction === 'asc' ? 'desc' : 'asc',
                                        )
                                    "
                                    scope="col"
                                    class="cursor-pointer px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300 hover:bg-gray-700"
                                >
                                    Mobile
                                    <span v-if="currentSort.field === 'mobile'" class="ml-1">
                                        {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </th>
                                <th
                                    @click="
                                        sortAndPaginate(
                                            1,
                                            perPage,
                                            'country',
                                            currentSort.field === 'country' && currentSort.direction === 'asc' ? 'desc' : 'asc',
                                        )
                                    "
                                    scope="col"
                                    class="cursor-pointer px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300 hover:bg-gray-700"
                                >
                                    Country
                                    <span v-if="currentSort.field === 'country'" class="ml-1">
                                        {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </th>
                                <th
                                    @click="
                                        sortAndPaginate(
                                            1,
                                            perPage,
                                            'gender',
                                            currentSort.field === 'gender' && currentSort.direction === 'asc' ? 'desc' : 'asc',
                                        )
                                    "
                                    scope="col"
                                    class="cursor-pointer px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300 hover:bg-gray-700"
                                >
                                    Gender
                                    <span v-if="currentSort.field === 'gender'" class="ml-1">
                                        {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700 bg-gray-800">
                            <tr v-for="client in pendingClients.data" :key="client.id" class="hover:bg-gray-700">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm font-medium text-gray-200">{{ client.name }}</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm text-gray-300">{{ client.email }}</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm text-gray-300">{{ client.mobile || 'N/A' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm text-gray-300">{{ client.country || 'N/A' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm text-gray-300">{{ client.gender || 'N/A' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button
                                            @click="approveClient(client.id)"
                                            class="rounded-md bg-green-700 px-3 py-1 text-sm font-medium text-white hover:bg-green-600"
                                        >
                                            Approve
                                        </button>
                                        <button
                                            @click="rejectClient(client.id)"
                                            class="rounded-md bg-red-800 px-3 py-1 text-sm font-medium text-white hover:bg-red-700"
                                        >
                                            Reject
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Enhanced Pagination for Pending Clients -->
                <div v-if="pendingClients.data.length > 0" class="mt-4 flex items-center justify-between">
                    <div class="text-sm text-gray-400">
                        Showing {{ pendingClients.from }} to {{ pendingClients.to }} of {{ pendingClients.total }} pending clients
                    </div>

                    <!-- Page Size Selector -->
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center">
                            <span class="mr-2 text-sm text-gray-400">Per page:</span>
                            <select
                                v-model="perPage"
                                @change="sortAndPaginate(1, perPage, currentSort.field, currentSort.direction)"
                                class="rounded-md bg-gray-700 px-2 py-1 text-sm text-gray-200"
                            >
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </div>

                        <!-- Page Navigation -->
                        <div class="flex space-x-2">
                            <button
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
                                :class="[
                                    'rounded-md px-3 py-1 text-sm',
                                    page.active
                                        ? 'bg-blue-600 text-white'
                                        : page.url
                                          ? 'bg-gray-700 text-gray-200 hover:bg-gray-600'
                                          : 'cursor-not-allowed bg-gray-800 text-gray-500',
                                ]"
                                v-html="page.label"
                            ></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Confirmation Dialog -->
            <div v-if="showConfirmDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                <div class="w-full max-w-md rounded-lg bg-gray-800 p-6 text-gray-200 shadow-xl">
                    <h3 class="text-xl font-semibold text-gray-100">{{ confirmDialogTitle }}</h3>
                    <p class="mt-2 text-gray-400">{{ confirmDialogMessage }}</p>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            class="rounded-md border border-gray-600 bg-gray-700 px-4 py-2 text-sm font-medium text-gray-200 hover:bg-gray-600"
                            @click="cancelConfirmation"
                        >
                            Cancel
                        </button>
                        <button
                            :class="[
                                'rounded-md px-4 py-2 text-sm font-medium text-white',
                                confirmAction === 'approve' ? 'bg-green-700 hover:bg-green-600' : 'bg-red-700 hover:bg-red-600',
                            ]"
                            @click="confirmAction === 'approve' ? confirmApprove() : confirmReject()"
                        >
                            {{ confirmAction === 'approve' ? 'Approve' : 'Reject' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Approval Data Tables -->
            <ApprovalDataTable :stats="approvalStats" :recent-approvals="recentApprovals" :pending-reservations="pendingReservations" />

            <!-- Debug Information (Only visible in development) -->
            <div class="mt-8 rounded-lg border border-red-700 bg-red-900 p-6">
                <h3 class="mb-4 text-xl font-semibold text-gray-100">Debug Information</h3>
                <div class="overflow-x-auto">
                    <p class="mb-2 text-sm text-gray-300">isAdmin prop: {{ isAdmin }}</p>
                    <p class="mb-2 text-sm text-gray-300">userRole prop: {{ userRole }}</p>
                    <p class="mb-2 text-sm text-gray-300">Admin button should show: {{ isAdmin }}</p>
                </div>
            </div>

            <!-- Client Approval Summary -->
            <div class="mt-8 rounded-lg border border-gray-700 bg-gray-800 p-4">
                <h3 class="mb-2 text-xl font-semibold text-gray-100">Client Approval Summary</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="rounded bg-gray-700 p-3">
                        <span class="text-gray-300">Pending Approvals:</span>
                        <span class="ml-2 font-semibold text-white">{{ pendingClients.total }}</span>
                    </div>
                    <div class="rounded bg-gray-700 p-3">
                        <span class="text-gray-300">Total Approved Clients:</span>
                        <span class="ml-2 font-semibold text-white">{{ approvedClientsCount }}</span>
                    </div>
                    <div class="rounded bg-gray-700 p-3">
                        <span class="text-gray-300">Your Approved Clients:</span>
                        <span class="ml-2 font-semibold text-white">{{ myApprovedClientsCount }}</span>
                    </div>
                </div>
                <div class="mt-4 rounded bg-blue-900 bg-opacity-50 p-3">
                    <p class="text-gray-200">
                        <strong>Note:</strong> After approving a client, they will appear in your "My Approved Clients" list and can make
                        reservations.
                    </p>
                    <div class="mt-2 flex flex-wrap gap-3">
                        <a
                            href="/receptionist/clients/my-approved"
                            class="inline-block rounded-lg bg-green-600 px-4 py-2 font-semibold text-white transition hover:bg-green-700"
                        >
                            View My Approved Clients ({{ myApprovedClientsCount }})
                        </a>
                        <a
                            href="/receptionist/clients/reservations"
                            class="inline-block rounded-lg bg-purple-600 px-4 py-2 font-semibold text-white transition hover:bg-purple-700"
                        >
                            View Client Reservations
                        </a>
                    </div>
                </div>
            </div>
        </div>
      </div>



      <!-- Confirmation Dialog -->
      <div v-if="showConfirmDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="w-full max-w-md rounded-lg bg-gray-800 p-6 text-gray-200 shadow-xl">
          <h3 class="text-xl font-semibold text-gray-100">{{ confirmDialogTitle }}</h3>
          <p class="mt-2 text-gray-400">{{ confirmDialogMessage }}</p>
          <div class="mt-6 flex justify-end space-x-3">
            <button
              class="rounded-md border border-gray-600 bg-gray-700 px-4 py-2 text-sm font-medium text-gray-200 hover:bg-gray-600"
              @click="cancelConfirmation"
            >
              Cancel
            </button>
            <button
              :class="[
                'rounded-md px-4 py-2 text-sm font-medium text-white',
                confirmAction === 'approve' 
                  ? 'bg-green-700 hover:bg-green-600' 
                  : 'bg-red-700 hover:bg-red-600'
              ]"
              @click="confirmAction === 'approve' ? confirmApprove() : confirmReject()"
            >
              {{ confirmAction === 'approve' ? 'Approve' : 'Reject' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Approval Data Tables -->
      <ApprovalDataTable
        :stats="approvalStats"
        :recent-approvals="recentApprovals"
        :pending-reservations="pendingReservations"
      />

      <!-- Debug Information (Only visible in development) -->
      <div class="mt-8 p-6 bg-red-900 rounded-lg border border-red-700">
        <h3 class="text-xl font-semibold mb-4 text-gray-100">Debug Information</h3>
        <div class="overflow-x-auto">
          <p class="text-sm text-gray-300 mb-2">isAdmin prop: {{ isAdmin }}</p>
          <p class="text-sm text-gray-300 mb-2">userRole prop: {{ userRole }}</p>
          <p class="text-sm text-gray-300 mb-2">Admin button should show: {{ isAdmin }}</p>
        </div>
      </div>

      <!-- Client Approval Summary -->
      <div class="mt-8 p-4 bg-gray-800 rounded-lg border border-gray-700">
        <h3 class="text-xl font-semibold mb-2 text-gray-100">Client Approval Summary</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="p-3 bg-gray-700 rounded">
            <span class="text-gray-300">Pending Approvals:</span>
            <span class="ml-2 text-white font-semibold">{{ pendingClients.total }}</span>
          </div>
          <div class="p-3 bg-gray-700 rounded">
            <span class="text-gray-300">Total Approved Clients:</span>
            <span class="ml-2 text-white font-semibold">{{ approvedClientsCount }}</span>
          </div>
          <div class="p-3 bg-gray-700 rounded">
            <span class="text-gray-300">Your Approved Clients:</span>
            <span class="ml-2 text-white font-semibold">{{ myApprovedClientsCount }}</span>
          </div>
        </div>
        <div class="mt-4 p-3 bg-blue-900 bg-opacity-50 rounded">
          <p class="text-gray-200">
            <strong>Note:</strong> After approving a client, they will appear in your "My Approved Clients" list and can make reservations.
          </p>
          <div class="mt-2 flex flex-wrap gap-3">
            <button
              @click="navigateTo('/receptionist/clients/my-approved')"
              class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
            >
              View My Approved Clients ({{ myApprovedClientsCount }})
            </button>
            <button
              @click="navigateTo('/receptionist/clients/reservations')"
              class="inline-flex items-center justify-center rounded-md bg-secondary px-4 py-2 text-sm font-medium text-secondary-foreground shadow-sm transition-colors hover:bg-secondary/80 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
            >
              View All My Clients Reservations
            </button>
          </div>
        </div>
      </div>
    </div>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';
import ApprovalDataTable from './ApprovalDataTable.vue';
defineOptions({ layout: AppLayout });
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
  router.get(url, {}, {
    preserveScroll: false,
    preserveState: false,
    replace: false
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
    selectedClientId.value = clientId;
    confirmAction.value = 'approve';
    confirmDialogTitle.value = 'Confirm Client Approval';
    confirmDialogMessage.value = 'Are you sure you want to approve this client? They will be added to your approved clients list.';
    showConfirmDialog.value = true;
};

const rejectClient = (clientId) => {
    selectedClientId.value = clientId;
    confirmAction.value = 'reject';
    confirmDialogTitle.value = 'Confirm Client Rejection';
    confirmDialogMessage.value = 'Are you sure you want to reject this client? This action cannot be undone.';
    showConfirmDialog.value = true;
};

const cancelConfirmation = () => {
    showConfirmDialog.value = false;
    selectedClientId.value = null;
};

const confirmApprove = async () => {
    if (!selectedClientId.value) return;

    try {
        // Show loading state
        showConfirmDialog.value = false;

        // Use axios to make the request
        const response = await axios.post(`/receptionist/clients/${selectedClientId.value}/approve`);
        console.log('Client approval response:', response.data);

        // If the client was approved successfully, update the data tables
        if (response.data.success) {
            // Add the newly approved client to the recentApprovals
            if (response.data.client) {
                props.recentlyApprovedClients.unshift(response.data.client);
                // Keep only the first 5 items
                if (props.recentlyApprovedClients.length > 5) {
                    props.recentlyApprovedClients.pop();
                }
            }

            // Add the updated reservations to pendingReservations
            if (response.data.updatedReservations) {
                props.pendingReservationsForApprovedClients.unshift(...response.data.updatedReservations);
                // Keep only the first 5 items
                if (props.pendingReservationsForApprovedClients.length > 5) {
                    props.pendingReservationsForApprovedClients.splice(5);
                }
            }

            // Update the stats
            props.approvedClientsCount++;
        }

        // Use Inertia router to reload the page with fresh data
        router.visit(window.location.pathname, {
            method: 'get',
            preserveScroll: false,
            preserveState: false,
            replace: true,
            onSuccess: () => {
                console.log('Client approved successfully');
            },
        });
    } catch (error) {
        console.error('Error approving client:', error);
        alert('Could not approve client due to a technical issue. Please try refreshing the page.');
    }
};

const confirmReject = async () => {
    if (!selectedClientId.value) return;

    try {
        // Show loading state
        showConfirmDialog.value = false;

        // Use axios to make the request
        await axios.post(`/receptionist/clients/${selectedClientId.value}/reject`);

        // Use Inertia router to reload the page with fresh data
        router.visit(window.location.pathname, {
            method: 'get',
            preserveScroll: false,
            preserveState: false,
            replace: true,
            onSuccess: () => {
                console.log('Client rejected successfully');
            },
        });
    } catch (error) {
        console.error('Error rejecting client:', error);
        alert('Could not reject client due to a technical issue. Please try refreshing the page.');
    }
};
</script>

<style scoped>
.pagination-link {
    @apply rounded-md px-3 py-1 text-sm;
}

.pagination-link-active {
    @apply bg-blue-600 text-white;
}

.pagination-link-inactive {
    @apply bg-gray-700 text-gray-200 hover:bg-gray-600;
}

.pagination-link-disabled {
    @apply cursor-not-allowed bg-gray-800 text-gray-500;
}
</style>
