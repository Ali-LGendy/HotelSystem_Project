<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

import Chart from 'chart.js/auto';
import { defineProps, onMounted, ref } from 'vue';
const props = defineProps({
    menuLinks: Array,
    total_reservations: Number,
    total_managers: Number,
    total_clients: Number,
    total_receptionists: Number,
    baned_users: Number,
    approved_clients: Number,
    pending_clients: Number,
});
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const stats = ref({
    totalClients: props.total_clients,
    totalManagers: props.total_managers,
    totalReservations: props.total_reservations,
    receptionists: props.total_receptionists,
    bannedUsers: props.baned_users,
    approvedClients: props.approved_clients,
    penddingClients: props.pending_clients,
});

const clientStatusChart = ref(null);
const monthlyReservationsChart = ref(null);

const navigateTo = (path) => {
    // Replace with your navigation logic (e.g., using Vue Router)
    console.log('Navigate to:', path);
};

onMounted(() => {
    // Client Status Pie Chart
    if (clientStatusChart.value) {
        new Chart(clientStatusChart.value, {
            type: 'pie',
            data: {
                labels: ['Approved', 'Banned', 'Pending'],
                datasets: [
                    {
                        data: [props.approved_clients, props.baned_users, props.pending_clients],
                        backgroundColor: ['rgba(75, 192, 192, 0.6)', 'rgba(255, 99, 132, 0.6)', 'rgba(255, 205, 86, 0.6)'],
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                },
            },
        });
    }

    // Monthly Reservations Bar Chart
    if (monthlyReservationsChart.value) {
        new Chart(monthlyReservationsChart.value, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [
                    {
                        label: 'Reservations',
                        data: [520, 612, 435, 678, 590, 565],
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    },
                ],
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                    },
                },
                plugins: {
                    legend: {
                        display: false,
                    },
                },
            },
        });
    }
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs" :menuLinks="props.menuLinks">
        <div class="mx-auto max-w-7xl px-4 py-8">
            <div class="rounded-lg bg-gray-900 p-8 text-gray-200 shadow-lg">
                <!-- Header -->
                <div class="mb-8 flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h2 class="text-3xl font-bold">Comprehensive System Metrics</h2>
                    </div>
                </div>

                <!-- Main Statistics Grid -->
                <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                    <div class="rounded-lg border border-gray-700 bg-gray-800 p-6 text-center">
                        <h3 class="text-4xl font-bold">{{ stats.totalClients }}</h3>
                        <p class="mt-2 text-gray-400">Total Clients</p>
                    </div>

                    <div class="rounded-lg border border-gray-700 bg-gray-800 p-6 text-center">
                        <h3 class="text-4xl font-bold">{{ stats.totalManagers }}</h3>
                        <p class="mt-2 text-gray-400">Total Managers</p>
                    </div>

                    <div class="rounded-lg border border-gray-700 bg-gray-800 p-6 text-center">
                        <h3 class="text-4xl font-bold">{{ stats.totalReservations }}</h3>
                        <p class="mt-2 text-gray-400">Total Reservations</p>
                    </div>
                </div>

                <!-- Additional Statistics -->
                <div class="mt-8 grid grid-cols-1 gap-8 md:grid-cols-3">
                    <div class="rounded-lg border border-gray-700 bg-gray-800 p-6 text-center">
                        <h3 class="text-4xl font-bold">{{ stats.receptionists }}</h3>
                        <p class="mt-2 text-gray-400">Total Receptionists</p>
                    </div>

                    <div class="rounded-lg border border-gray-700 bg-gray-800 p-6 text-center">
                        <h3 class="text-4xl font-bold">{{ stats.bannedUsers }}</h3>
                        <p class="mt-2 text-gray-400">Banned Users</p>
                    </div>

                    <div class="rounded-lg border border-gray-700 bg-gray-800 p-6 text-center">
                        <h3 class="text-4xl font-bold">{{ stats.approvedClients }}</h3>
                        <p class="mt-2 text-gray-400">Approved Clients</p>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="mt-8 grid grid-cols-1 gap-8 md:grid-cols-2">
                    <!-- Client Status Pie Chart -->
                    <div class="rounded-lg border border-gray-700 bg-gray-800 p-6">
                        <h3 class="mb-4 text-center text-xl font-semibold">Client Status Distribution</h3>
                        <div class="flex justify-center">
                            <canvas ref="clientStatusChart"></canvas>
                        </div>
                    </div>

                    <!-- Reservations Bar Chart -->
                    <div class="rounded-lg border border-gray-700 bg-gray-800 p-6">
                        <h3 class="mb-4 text-center text-xl font-semibold">Monthly Reservations</h3>
                        <div class="flex justify-center">
                            <canvas ref="monthlyReservationsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
