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
    form.post(route('admin.users.managers.store'), {
        onSuccess: () => router.visit(route('admin.users.managers.index')),
    });
};

const cancelForm = () => {
    router.visit(route('admin.users.managers.index'));
};
</script>

<template>
    <div class="min-h-screen bg-background p-8 text-foreground">
        <div class="mx-auto max-w-2xl">
            <Card>
                <CardHeader>
                    <CardTitle>Create New Manager</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submitForm" enctype="multipart/form-data" class="space-y-6">
                        <!-- Name -->
                        <div>
                            <Label for="name">Name</Label>
                            <Input v-model="form.name" id="name" placeholder="Enter manager's name" required />
                        </div>

                        <!-- Email -->
                        <div>
                            <Label for="email">Email</Label>
                            <Input v-model="form.email" id="email" type="email" placeholder="Enter manager's email" required />
                        </div>

                        <!-- Password -->
                        <div>
                            <Label for="password">Password</Label>
                            <Input v-model="form.password" id="password" type="password" placeholder="Set a password" required />
                        </div>

                        <!-- National ID -->
                        <div>
                            <Label for="national_id">National ID</Label>
                            <Input v-model="form.national_id" id="national_id" placeholder="Enter national ID" required />
                        </div>

                        <!-- Avatar Image -->
                        <div>
                            <Label for="avatar_img">Avatar Image</Label>
                            <Input id="avatar_img" type="file" @change="handleFileUpload" accept="image/*" />
                        </div>
                        <div>
                            <Label for="gender">Gender</Label>
                            <div class="relative">
                                <button
                                    type="button"
                                    @click="showGenderDropdown = !showGenderDropdown"
                                    class="flex w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-left"
                                >
                                    {{ form.gender || 'Select Gender' }}
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="h-4 w-4 opacity-50"
                                    >
                                        <path d="m6 9 6 6 6-6" />
                                    </svg>
                                </button>

                                <div
                                    v-if="showGenderDropdown"
                                    class="absolute z-10 mt-1 w-full rounded-md border border-input bg-background shadow-md"
                                >
                                    <div
                                        v-for="option in genderOptions"
                                        :key="option"
                                        @click="selectGender(option)"
                                        class="cursor-pointer px-3 py-2 hover:bg-muted"
                                    >
                                        {{ option }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Submit and Cancel Buttons -->
                        <div class="flex space-x-4">
                            <Button type="submit" class="flex-1">Create Manager</Button>
                            <Button type="button" variant="outline" @click="cancelForm" class="flex-1"> Cancel </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
