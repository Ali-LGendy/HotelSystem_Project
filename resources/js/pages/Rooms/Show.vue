<template>
    <div class="min-h-screen bg-background p-8 text-foreground">
        <div class="mx-auto max-w-3xl overflow-hidden rounded-lg border border-border bg-card text-card-foreground shadow-md">
            <div class="p-6">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-3xl font-bold">Room Details</h1>
                    <div class="flex space-x-2">
                        <Button @click="navigateToEdit" variant="default"> Edit </Button>
                        <Button @click="navigateToIndex" variant="outline"> Back to List </Button>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="rounded-lg border border-border bg-accent/10 p-4">
                        <h2 class="mb-4 text-lg font-semibold">Room Information</h2>

                        <div class="mb-4">
                            <p class="text-sm text-muted-foreground">Room Number</p>
                            <p class="text-lg font-medium">{{ room.room_number }}</p>
                        </div>

                        <div class="mb-4">
                            <p class="text-sm text-muted-foreground">Capacity</p>
                            <p class="text-lg font-medium">{{ room.room_capacity }} person(s)</p>
                        </div>

                        <div class="mb-4">
                            <p class="text-sm text-muted-foreground">Price</p>
                            <p class="text-lg font-medium">${{ room.price }}</p>
                        </div>

                        <div class="mb-4">
                            <p class="text-sm text-muted-foreground">Status</p>
                            <span :class="getStatusClass(room.status)" class="rounded-full px-3 py-1 text-sm font-medium">
                                {{ room.status }}
                            </span>
                        </div>
                    </div>

                    <div class="rounded-lg border border-border bg-accent/10 p-4">
                        <h2 class="mb-4 text-lg font-semibold">Location & Management</h2>

                        <div class="mb-4" v-if="room.floor">
                            <p class="text-sm text-muted-foreground">Floor</p>
                            <p class="text-lg font-medium">{{ room.floor.name }} ({{ room.floor.number }})</p>
                        </div>

                        <div class="mb-4" v-if="room.manager">
                            <p class="text-sm text-muted-foreground">Manager</p>
                            <p class="text-lg font-medium">{{ room.manager.name }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    room: Object,
});

const getStatusClass = (status) => {
    switch (status) {
        case 'available':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'occupied':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        case 'maintenance':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
};

const navigateToEdit = () => {
    router.get(route('rooms.edit', props.room.id));
};

const navigateToIndex = () => {
    router.get(route('rooms.index'));
};
</script>
