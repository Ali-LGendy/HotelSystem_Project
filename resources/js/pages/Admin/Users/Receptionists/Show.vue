<template>
    <div class="flex min-h-screen items-center justify-center bg-background p-8 text-foreground">
        <div class="w-full max-w-3xl">
            <Card class="w-full p-6">
                <CardHeader>
                    <CardTitle class="text-3xl font-bold">Receptionist Details</CardTitle>
                </CardHeader>

                <CardContent class="space-y-6">
                    <!-- Avatar -->
                    <div class="flex items-center space-x-6">
                        <img :src="getImageUrl(user.data.avatar_img)" alt="User Avatar" class="h-32 w-32 rounded-full border-4 border-gray-300" />
                        <div>
                            <h2 class="text-3xl font-bold">{{ user.data.name }}</h2>
                            <p class="text-xl text-gray-600 dark:text-gray-400">{{ user.data.email }}</p>
                        </div>
                    </div>

                    <!-- User Information -->
                    <div class="mt-6 grid grid-cols-2 gap-6">
                        <div>
                            <p class="mb-2 text-sm font-semibold text-gray-500">National ID:</p>
                            <p class="text-lg font-medium">{{ user.data.national_id }}</p>
                        </div>

                        <div>
                            <p class="mb-2 text-sm font-semibold text-gray-500">Status:</p>
                            <p
                                class="inline-block rounded-full px-4 py-1 text-sm font-semibold"
                                :class="user.data.is_banned ? 'bg-red-500 text-white' : 'bg-green-500 text-white'"
                            >
                                {{ user.data.is_banned ? 'Banned' : 'Active' }}
                            </p>
                        </div>

                        <div>
                            <p class="mb-2 text-sm font-semibold text-gray-500">Created At:</p>
                            <p class="text-lg font-medium">{{ new Date(user.created_at).toLocaleString() }}</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-8 flex space-x-4">
                        <Button @click="router.visit(route('receptionist.index'))" variant="outline"> Back to List </Button>
                        <Button @click="router.visit(route('receptionist.edit', user.data.id))" variant="primary"> Edit User </Button>
                        <Button @click="deleteUser" variant="destructive"> Delete Receptionist </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>

<script setup>
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { defineOptions, defineProps, onMounted } from 'vue';
import { route } from 'ziggy-js';

defineOptions({ layout: AppLayout });

// Props to receive user data
const props = defineProps({
    user: {
        type: Object,
        required: true,
        validator: (value) => value.data && value.data.id,
    },
});

onMounted(() => {
    console.log('User Data:', props.user);
});

// Helper function to format the avatar URL
const getImageUrl = (path) => {
    // If path is null or undefined
    if (!path) return '/defaults/user.png';

    // For full URLs
    if (typeof path === 'string' && path.startsWith('http')) {
        return path;
    }

    // For local storage files
    return `/storage/${path}`;
};

// Delete user (with confirmation)
const deleteUser = () => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(route('receptionist.destroy', props.user.data.id), {
            onSuccess: () => router.visit(route('receptionist.index')),
        });
    }
};
</script>
