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
const props = defineProps({ manager: Object });

defineOptions({ layout: AppLayout });

// Initialize form with existing manager data
const form = useForm({
    _method: 'PUT',
    name: props.manager.name || '', // Ensure non-null value
    email: props.manager.email || '', // Ensure non-null value
    password: '', // Keep as optional
    national_id: props.manager.national_id || '', // Ensure non-null value
    avatar_img: props.manager.avatar_img || null,
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

    // Handle file upload
    if (form.avatar_img instanceof File) {
        formData.append('avatar_img', form.avatar_img);
    }

    // Use axios directly for more control
    router.post(route('manager.update', props.manager.id), formData, {
        forceFormData: true, // Important for file uploads
        onSuccess: () => {
            console.log('Update successful');
            router.visit(route('manager.index'));
        },
        onError: (errors) => {
            console.error('Update errors:', errors);
        },
        preserveScroll: true,
        preserveState: true,
    });
};

const cancelForm = () => {
    router.visit(route('manager.index'));
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
    const managerName = props.manager.name;
    event.target.style.display = 'none';
    event.target.parentNode.innerHTML = `
        <div class="h-20 w-20 rounded-full bg-gray-300 flex items-center justify-center text-xl font-bold">
            ${getInitials(managerName)}
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
    <div class="flex min-h-screen items-center justify-center bg-background p-8 text-foreground">
        <div class="w-full max-w-3xl">
            <Card class="w-full p-6">
                <CardHeader>
                    <CardTitle class="text-3xl font-bold">Update Manager</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submitForm" enctype="multipart/form-data" class="space-y-6">
                        <div class="grid grid-cols-2 gap-6">
                            <!-- Name -->
                            <div>
                                <Label for="name" class="text-lg">Name</Label>
                                <Input v-model="form.name" id="name" placeholder="Enter manager's name" required class="mt-2 h-12" />
                                <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <!-- Email -->
                            <div>
                                <Label for="email" class="text-lg">Email</Label>
                                <Input v-model="form.email" id="email" type="email" placeholder="Enter manager's email" required class="mt-2 h-12" />
                                <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">
                                    {{ form.errors.email }}
                                </p>
                            </div>

                            

                            <!-- National ID -->
                            <div>
                                <Label for="national_id" class="text-lg">National ID</Label>
                                <Input v-model="form.national_id" id="national_id" placeholder="Enter national ID" required class="mt-2 h-12" />
                                <p v-if="form.errors.national_id" class="mt-1 text-sm text-red-500">
                                    {{ form.errors.national_id }}
                                </p>
                            </div>

                            <!-- Avatar Image -->
                            <div class="col-span-2">
                                <Label for="avatar_img" class="text-lg">Avatar Image</Label>
                                <Input id="avatar_img" type="file" @change="handleFileUpload" accept="image/*" class="mt-2" />
                                <div class="mt-4">
                                    <p class="mb-2 text-lg">Current Avatar:</p>
                                    <img
                                        :src="getImageUrl(form.avatar_img)"
                                        alt="Avatar"
                                        class="h-20 w-20 rounded-full border border-gray-500"
                                        @error="handleImageError"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Submit and Cancel Buttons -->
                        <div class="mt-6 flex space-x-4">
                            <Button type="button" variant="outline" @click="cancelForm" class="h-12 flex-1 text-lg"> Cancel </Button>
                            <Button type="submit" class="h-12 flex-1 text-lg">Update Manager</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
