<template>
    <div class="mx-auto max-w-2xl rounded-lg bg-white p-8 shadow-lg">
        <h2 class="mb-6 text-2xl font-semibold">Create New Receptionist</h2>

        <form @submit.prevent="submitForm" enctype="multipart/form-data">
            <!-- Name -->
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium">Name</label>
                <input v-model="form.name" type="text" id="name" required class="mt-1 block w-full rounded-lg border border-gray-300 p-2" />
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium">Email</label>
                <input v-model="form.email" type="email" id="email" required class="mt-1 block w-full rounded-lg border border-gray-300 p-2" />
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium">Password</label>
                <input
                    v-model="form.password"
                    type="password"
                    id="password"
                    required
                    class="mt-1 block w-full rounded-lg border border-gray-300 p-2"
                />
            </div>

            <!-- National ID -->
            <div class="mb-4">
                <label for="national_id" class="block text-sm font-medium">National ID</label>
                <input
                    v-model="form.national_id"
                    type="text"
                    id="national_id"
                    required
                    class="mt-1 block w-full rounded-lg border border-gray-300 p-2"
                />
            </div>

            <!-- Avatar Image -->
            <div class="mb-4">
                <label for="avatar_image" class="block text-sm font-medium">Avatar Image</label>
                <input
                    type="file"
                    id="avatar_image"
                    @change="handleFileUpload"
                    accept="image/*"
                    class="mt-1 block w-full rounded-lg border border-gray-300 p-2"
                />
            </div>

            <!-- Submit Button -->
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 font-bold text-white hover:bg-blue-700">Create User</button>
        </form>
    </div>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue';
import { router, useForm } from '@inertiajs/vue3';

// defineOptions({ layout: AdminLayout });

export default {
    name: 'CreateUserForm',
    layout: AppLayout,
    setup() {
        const form = useForm({
            name: '',
            email: '',
            password: '',
            national_id: '',
            avatar_image: null,
        });

        const handleFileUpload = (event) => {
            form.avatar_image = event.target.files[0];
        };

        const submitForm = () => {
            form.post(route('admin.users.receptionists.store'), {
                onSuccess: () => router.visit(route('admin.users.receptionists.index')),
            });
        };

        return { form, handleFileUpload, submitForm };
    },
};
</script>

<style scoped>
label {
    color: #4a4a4a;
}
</style>
