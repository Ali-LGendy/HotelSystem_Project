<script setup>
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { defineProps, onMounted } from 'vue';
import { route } from 'ziggy-js';

// Props for passing manager data

const props = defineProps({
    clients: {
        type: Object, // Changed from Array to Object since it's paginated
        default: () => ({ data: [] }),
    },
});
onMounted(() => {
    console.log('DOM is ready');
    console.log('Managers data:', props.clients);
});
defineOptions({ layout: AppLayout });
// Function to handle deletion (for demonstration)
// const deleteManager = (id) => {
//     if (confirm('Are you sure you want to delete this manager?')) {
//         // Example: Implement deletion logic with Inertia
//         router.delete(route('admin.users.managers.destroy', id));
//     }
// };
</script>

<template>
    <div class="min-h-screen rounded-lg bg-gray-900 p-8 text-gray-200 shadow-lg">
        <h1 class="mb-8 text-3xl font-bold">Manage Clients</h1>

        <!-- Add Manager Button -->
        <!-- <Link :href="route('admin.users.managers.create')">
            <Button class="mb-6">Add Manager</Button>
        </Link> -->

        <!-- Managers Table -->
        <Table v-if="clients && clients.data.length > 0" class="w-full overflow-hidden rounded-lg border border-gray-700">
            <TableHeader>
                <TableRow>
                    <TableHead>Name</TableHead>
                    <TableHead>Email</TableHead>
                    <TableHead>Password</TableHead>
                    <TableHead>National ID</TableHead>
                    <TableHead>Avatar Image</TableHead>
                    <TableHead>Actions</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <TableRow v-for="client in clients.data" :key="client.id" class="transition hover:bg-gray-800">
                    <TableCell>{{ client.name }}</TableCell>
                    <TableCell>{{ client.email }}</TableCell>
                    <TableCell>{{ client.password }}</TableCell>
                    <TableCell>{{ client.national_id }}</TableCell>
                    <TableCell class="border-t border-gray-700 p-4">
                        <img v-if="client.avatar_img" :src="client.avatar_img" alt="Avatar" class="h-10 w-10 rounded-full border border-gray-600" />
                        <span v-else class="text-gray-500">No Avatar</span>
                    </TableCell>
                    <TableCell>
                        <div class="flex gap-4">
                            <!-- Edit Button -->
                            <Link :href="route('admin.users.managers.edit', client)">
                                <Button variant="outline">Edit</Button>
                            </Link>

                            <!-- Delete Button -->
                            <Link
                                method="delete"
                                :href="route('admin.users.managers.destroy', client)"
                                as="button"
                                class="text-red-500 transition-colors duration-200 hover:text-red-400"
                            >
                                <Button variant="destructive">Delete</Button>
                            </Link>
                        </div>
                    </TableCell>
                </TableRow>

                <!-- Empty State -->
                <TableRow v-show="clients.length === 0">
                    <TableCell colspan="5" class="py-4 text-center text-gray-500">No Clients found.</TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
