<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    password: '',
    gender: '',
    password_confirmation: '',
    avatar_image: '',
    country: '  ',
});

const submit = () => {
    form.post(route('register'), {
        // forceFormData: true, // Ensures the file is sent correctly
        onFinish: () => form.reset('password', 'password_confirmation'),
        // forceFormData: true // Required for file uploads
    });
};

const countries = ['USA', 'UK', 'Canada', 'Germany', 'France'];
</script>

<template>
    <AuthBase title="Create an account" description="Enter your details below to create your account">
        <Head title="Register" />

        <form @submit.prevent="submit" class="flex flex-col gap-6" enctype="multipart/form-data">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input id="name" type="text" autofocus :tabindex="1" autocomplete="name" v-model="form.name" placeholder="Full name" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input id="email" type="email" :tabindex="2" autocomplete="email" v-model="form.email" placeholder="email@example.com" />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="avatar_image">Avatar Image</Label>
                    <Input id="avatar_image" type="file" name="avatar_image" :tabindex="1" @change="form.avatar_image = $event.target.files[0]" />
                    <InputError :message="form.errors.avatar_image" />
                </div>

                <div class="grid gap-3">
                    <Label for="gender">Gender</Label>

                    <div class="flex gap-10 ps-16">
                        <div class="flex gap-3">
                            <Input id="male" type="radio" name="gender" value="male" v-model="form.gender" />
                            <Label for="male">Male</Label>
                        </div>
                        <div class="flex gap-3">
                            <Input id="female" type="radio" name="gender" value="female" v-model="form.gender" />
                            <Label for="female">Female</Label>
                        </div>
                    </div>

                    <InputError :message="form.errors.gender" />
                </div>

                <div class="grid gap-3">
                    <Label for="country">Country</Label>
                    <Select v-model="form.country" id="country">
                        <option v-for="country in countries" :key="country" :value="country">
                            {{ country }}
                        </option>
                    </Select>
                    <InputError :message="form.errors.country" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input id="password" type="password" :tabindex="3" autocomplete="new-password" v-model="form.password" placeholder="Password" />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        :tabindex="4"
                        autocomplete="new-password"
                        v-model="form.password_confirmation"
                        placeholder="Confirm password"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <Button type="submit" class="mt-2 w-full" tabindex="5" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Create account
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink :href="route('login')" class="underline underline-offset-4" :tabindex="6">Log in</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
