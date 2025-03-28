<template>
    <div class="min-h-screen bg-background p-8 text-foreground">
        <div class="mx-auto max-w-lg rounded-lg border border-border bg-card p-6 text-card-foreground shadow-md">
            <h1 class="mb-8 text-3xl font-bold text-foreground">Create New Room</h1>

            <!-- Form errors -->
            <div v-if="Object.keys(errors).length > 0" class="mb-6 rounded-r-lg border-l-4 border-destructive bg-destructive/10 p-4">
                <div v-for="(error, key) in errors" :key="key" class="text-destructive-foreground">
                    {{ error }}
                </div>
            </div>

            <form @submit.prevent="submit">
                <div class="mb-4">
                    <label for="room_number" class="mb-2 block text-sm font-medium text-foreground"> Room Number </label>
                    <input
                        id="room_number"
                        v-model="form.room_number"
                        type="text"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-foreground focus:outline-none focus:ring-2 focus:ring-ring"
                        required
                    />
                </div>

                <div class="mb-4">
                    <label for="room_capacity" class="mb-2 block text-sm font-medium text-foreground"> Room Capacity </label>
                    <input
                        id="room_capacity"
                        v-model="form.room_capacity"
                        type="number"
                        min="1"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-foreground focus:outline-none focus:ring-2 focus:ring-ring"
                        required
                    />
                </div>

                <div class="mb-4">
                    <label for="price" class="mb-2 block text-sm font-medium text-foreground"> Price </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-muted-foreground">$</span>
                        <input
                            id="price"
                            v-model="form.price"
                            type="number"
                            min="0"
                            step="0.01"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 pl-8 text-foreground focus:outline-none focus:ring-2 focus:ring-ring"
                            required
                        />
                    </div>
                </div>

                <div class="mb-4">
                    <label for="status" class="mb-2 block text-sm font-medium text-foreground"> Status </label>
                    <select
                        id="status"
                        v-model="form.status"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-foreground focus:outline-none focus:ring-2 focus:ring-ring"
                        required
                    >
                        <option v-for="status in statuses" :key="status" :value="status">
                            {{ status.charAt(0).toUpperCase() + status.slice(1) }}
                        </option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="floor_id" class="mb-2 block text-sm font-medium text-foreground"> Floor </label>
                    <select
                        id="floor_id"
                        v-model="form.floor_id"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-foreground focus:outline-none focus:ring-2 focus:ring-ring"
                        required
                    >
                        <option value="">Select Floor</option>
                        <option v-for="floor in floors" :key="floor.id" :value="floor.id">{{ floor.name }} ({{ floor.number }})</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label for="manager_id" class="mb-2 block text-sm font-medium text-foreground"> Room Manager </label>
                    <select
                        id="manager_id"
                        v-model="form.manager_id"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-foreground focus:outline-none focus:ring-2 focus:ring-ring"
                    >
                        <option value="">No Manager</option>
                        <option v-for="manager in managers" :key="manager.id" :value="manager.id">
                            {{ manager.name }}
                        </option>
                    </select>
                </div>

                <div class="flex justify-end space-x-3">
                    <button
                        type="button"
                        @click="cancel"
                        class="rounded-md border border-input bg-background px-4 py-2 text-foreground hover:bg-accent focus:outline-none focus:ring-2 focus:ring-ring"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="rounded-md bg-primary px-4 py-2 text-primary-foreground hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-ring"
                    >
                        Create Room
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    managers: Array,
    floors: Array,
    statuses: Array,
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const form = useForm({
    room_number: '',
    room_capacity: '',
    price: '',
    status: 'available',
    floor_id: '',
    manager_id: '',
});

defineOptions({ layout: AppLayout });

const submit = () => {
    form.post(route('rooms.store'));
};

const cancel = () => {
    router.visit(route('rooms.index'), {
        method: 'get',
        preserveState: false,
        preserveScroll: false,
        only: [],
    });
};
</script>
