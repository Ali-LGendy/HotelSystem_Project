<script setup>
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import { defineOptions, defineProps } from 'vue';
import { route } from 'ziggy-js';

// Props from the controller (manager object)
const props = defineProps({ receptionist: Object });

defineOptions({ layout: AppLayout });

// Initialize form with existing manager data
const form = useForm({
    _method: 'PUT',
    name: props.receptionist.name || '', // Ensure non-null value
    email: props.receptionist.email || '', // Ensure non-null value
    password: '', // Keep as optional
    national_id: props.receptionist.national_id || '', // Ensure non-null value
    avatar_img: props.receptionist.avatar_img || null,
});

// Handle avatar upload
const handleFileUpload = (event) => {
    form.avatar_img = event.target.files[0];
};

const submitForm = () => {
    // Create a FormData object to handle file upload
    const formData = new FormData();

    // Append all form fields
    formData.append('_method', 'PUT');
    formData.append('name', form.name);
    formData.append('email', form.email);
    formData.append('national_id', form.national_id);

    // Only append password if it's not empty
    if (form.password) {
        formData.append('password', form.password);
    }

    // Handle file upload
    if (form.avatar_img instanceof File) {
        formData.append('avatar_img', form.avatar_img);
    }

    // Use axios directly for more control
    router.post(route('admin.users.receptionists.update', props.receptionist.id), formData, {
        forceFormData: true, // Important for file uploads
        onSuccess: () => {
            console.log('Update successful');
            router.visit(route('admin.users.receptionists.index'));
        },
        onError: (errors) => {
            console.error('Update errors:', errors);
        },
        preserveScroll: true,
        preserveState: true,
    });
};
const getImageUrl = (path) => {
    // If path is a File object
    if (path instanceof File) {
        return URL.createObjectURL(path);
    }

    // If path is null or undefined
    if (!path) return '/defaults/user.png';

    // For full URLs
    if (typeof path === 'string' && path.startsWith('http')) {
        return path;
    }

    // For local storage files
    return `/storage/${path}`;
};

// Fallback to initials if image fails to load
const handleImageError = (event) => {
    event.target.style.display = 'none';
    event.target.parentNode.innerHTML = `
        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
            ${getInitials(receptionist.name)}
        </div>
    `;
};

// Get initials from name
const getInitials = (name) => {
    return name
        .split(' ')
        .map((part) => part[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
};
</script>

<template>
    <Card class="mx-auto max-w-2xl rounded-lg bg-white shadow-lg dark:bg-gray-900">
        <CardHeader>
            <CardTitle class="text-gray-900 dark:text-gray-100">Update Manager</CardTitle>
        </CardHeader>
        <CardContent>
            <form @submit.prevent="submitForm" enctype="multipart/form-data" class="space-y-6">
                <!-- Name -->
                <div>
                    <Label for="name" class="dark:text-gray-300">Name</Label>
                    <Input v-model="form.name" id="name" placeholder="Enter manager's name" />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">
                        {{ form.errors.name }}
                    </p>
                </div>

                <!-- Email -->
                <div>
                    <Label for="email" class="dark:text-gray-300">Email</Label>
                    <Input v-model="form.email" id="email" type="email" placeholder="Enter manager's email" />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">
                        {{ form.errors.email }}
                    </p>
                </div>

                <!-- Password -->
                <div>
                    <Label for="password" class="dark:text-gray-300">Password</Label>
                    <Input v-model="form.password" id="password" type="password" placeholder="Set a password" />
                    <p v-if="form.errors.password" class="mt-1 text-sm text-red-500">
                        {{ form.errors.password }}
                    </p>
                </div>

                <!-- National ID -->
                <div>
                    <Label for="national_id" class="dark:text-gray-300">National ID</Label>
                    <Input v-model="form.national_id" id="national_id" placeholder="Enter national ID" />
                    <p v-if="form.errors.national_id" class="mt-1 text-sm text-red-500">
                        {{ form.errors.national_id }}
                    </p>
                </div>

                <!-- Avatar Image -->
                <div>
                    <label for="avatar_img" class="mb-2 block text-sm font-semibold">Avatar Image</label>
                    <input
                        type="file"
                        id="avatar_img"
                        @change="handleFileUpload"
                        accept="image/*"
                        class="w-full rounded-lg border border-gray-600 bg-gray-800 p-3"
                    />
                    <div class="mt-4">
                        <p class="mb-2">Current Avatar:</p>
                        <img
                            :src="getImageUrl(form.avatar_img)"
                            alt="Avatar"
                            class="h-20 w-20 rounded-full border border-gray-500"
                            @error="handleImageError"
                        />
                    </div>
                </div>

                <!-- Submit Button -->
                <Button type="submit" class="w-full">Update Manager</Button>
            </form>
        </CardContent>
    </Card>
</template>

<style scoped>
label {
    color: #4a4a4a;
}
</style>
