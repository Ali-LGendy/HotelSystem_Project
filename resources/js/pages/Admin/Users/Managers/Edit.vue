<script setup>
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import { defineOptions, defineProps } from 'vue';
// Props from the controller (manager object)
const props = defineProps({ manager: Object });

defineOptions({ layout: AppLayout });

// Initialize form with existing manager data
const form = useForm({
    name: props.manager.name,
    email: props.manager.email,
    password: props.manager.password,
    national_id: props.manager.national_id,
    avatar_image: null,
});

// Handle avatar upload
const handleFileUpload = (event) => {
    form.avatar_image = event.target.files[0];
};

// Submit form using PUT for update
const submitForm = () => {
    form.put(route('admin.users.managers.update', props.manager), {
        onSuccess: () => router.visit(route('admin.users.managers.index')),
    });
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
                    <Input v-model="form.name" id="name" placeholder="Enter manager's name" required />
                </div>

                <!-- Email -->
                <div>
                    <Label for="email" class="dark:text-gray-300">Email</Label>
                    <Input v-model="form.email" id="email" type="email" placeholder="Enter manager's email" required />
                </div>

                <!-- Password -->
                <div>
                    <Label for="password" class="dark:text-gray-300">Password</Label>
                    <Input v-model="form.password" id="password" type="password" placeholder="Set a password" required />
                </div>

                <!-- National ID -->
                <div>
                    <Label for="national_id" class="dark:text-gray-300">National ID</Label>
                    <Input v-model="form.national_id" id="national_id" placeholder="Enter national ID" required />
                </div>

                <!-- Avatar Image -->
                <!-- <div>
                    <label for="avatar_image" class="mb-2 block text-sm font-semibold">Avatar Image</label>
                    <input
                        type="file"
                        id="avatar_image"
                        @change="handleFileUpload"
                        accept="image/*"
                        class="w-full rounded-lg border border-gray-600 bg-gray-800 p-3"
                    />
                    <div v-if="manager.avatar_img" class="mt-4">
                        <p class="mb-2">Current Avatar:</p>
                        <img :src="manager.avatar_img" alt="Avatar" class="h-20 w-20 rounded-full border border-gray-500" />
                    </div>
                </div> -->

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
