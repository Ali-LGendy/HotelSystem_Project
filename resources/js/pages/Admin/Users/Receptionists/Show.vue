<template>
    <Card class="mx-auto max-w-3xl rounded-lg bg-white shadow-lg dark:bg-gray-900">
        <CardHeader>
            <CardTitle class="text-gray-900 dark:text-gray-100">Receptionist Details</CardTitle>
        </CardHeader>

        <CardContent class="space-y-6">
            <!-- Avatar -->
            <div class="flex items-center space-x-4">
                <img :src="getImageUrl(user.data.avatar_img)" alt="User Avatar" class="h-24 w-24 rounded-full border border-gray-600" />
                <div>
                    <h2 class="text-2xl font-semibold">{{ user.data.name }}</h2>
                    <p class="text-gray-500 dark:text-gray-400">{{ user.data.email }}</p>
                </div>
            </div>

            <!-- User Information -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <p class="text-sm font-semibold text-gray-400">National ID:</p>
                    <p class="text-lg">{{ user.data.national_id }}</p>
                </div>

                <div>
                    <p class="text-sm font-semibold text-gray-400">Status:</p>
                    <p
                        class="inline-block rounded-full px-3 py-1 text-sm"
                        :class="user.data.is_banned ? 'bg-red-500 text-white' : 'bg-green-500 text-white'"
                    >
                        {{ user.data.is_banned ? 'Banned' : 'Active' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm font-semibold text-gray-400">Created At:</p>
                    <p class="text-lg">{{ new Date(user.created_at).toLocaleString() }}</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-8 flex justify-end space-x-4">
                <Button @click="router.visit(route('admin.users.managers.index'))" variant="outline"> Back to List </Button>
                <Button @click="router.visit(route('admin.users.managers.edit', user.data.id))" variant="primary"> Edit User </Button>
                <Button @click="deleteUser" variant="destructive"> Delete Receptionist </Button>
            </div>
        </CardContent>
    </Card>
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
        router.delete(route('admin.users.receptionists.destroy', props.user.data.id), {
            onSuccess: () => router.visit(route('admin.users.receptionists.index')),
        });
    }
};
</script>
