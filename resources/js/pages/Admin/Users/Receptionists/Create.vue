<script setup>
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { router, useForm } from '@inertiajs/vue3';

defineOptions({ layout: AppLayout });

const form = useForm({
    name: '',
    email: '',
    password: '',
    national_id: '',
    avatar_img: null,
});

const handleFileUpload = (event) => {
    form.avatar_img = event.target.files[0];
};

const submitForm = () => {
    form.post(route('admin.users.receptionists.store'), {
        onSuccess: () => router.visit(route('admin.users.receptionists.index')),
    });
};
</script>

<template>
    <Card class="mx-auto max-w-2xl rounded-lg bg-white shadow-lg dark:bg-gray-900">
        <CardHeader>
            <CardTitle class="text-gray-900 dark:text-gray-100">Create New Manager</CardTitle>
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
                <div>
                    <Label for="avatar_img" class="dark:text-gray-300">Avatar Image</Label>
                    <Input id="avatar_img" type="file" @change="handleFileUpload" accept="image/*" />
                </div>

                <!-- Submit Button -->
                <Button type="submit" class="w-full">Create Manager</Button>
            </form>
        </CardContent>
    </Card>
</template>

<style scoped>
label {
    color: #4a4a4a;
}
</style>
