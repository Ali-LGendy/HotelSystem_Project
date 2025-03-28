<template>
    <div class="min-h-screen bg-background p-8 text-foreground">
        <div class="mx-auto max-w-lg rounded-lg border border-border bg-card p-6 text-card-foreground shadow-md">
            <h1 class="mb-8 text-3xl font-bold text-foreground">Create New Floor</h1>

            <!-- Form errors -->
            <div v-if="Object.keys(errors).length > 0" class="mb-6 rounded-r-lg border-l-4 border-destructive bg-destructive/10 p-4">
                <div v-for="(error, key) in errors" :key="key" class="text-destructive-foreground">
                    {{ error }}
                </div>
            </div>

            <form @submit.prevent="submit">
                <div class="mb-4">
                    <label for="name" class="mb-2 block text-sm font-medium text-foreground"> Floor Name </label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-foreground focus:outline-none focus:ring-2 focus:ring-ring"
                        required
                    />
                </div>

                <div class="mb-6">
                    <label for="manager_id" class="mb-2 block text-sm font-medium text-foreground"> Floor Manager </label>
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
                        Create Floor
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
defineOptions({ layout: AppLayout });
const props = defineProps({
    floor: Object,
    managers: Array,
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const form = useForm({
    name: props.floor?.name || '',
    number: props.floor?.number || '',
    manager_id: props.floor?.manager_id || '',
});

const submit = () => {
    form.post(route('floors.store'));
};

const cancel = () => {
    router.visit(route('floors.index'), {
        method: 'get',
        preserveState: false,
        preserveScroll: false,
        only: [],
    });
};
</script>
