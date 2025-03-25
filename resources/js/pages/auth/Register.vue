<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import   Select  from '@/Components/ui/input/Select.vue'; // Import the new Select component
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
    national_id: '',
});

const submit = () => {
    form.post(route('register'), {
        forceFormData: true, // Ensures the file is sent correctly
        onFinish: () => form.reset('password', 'password_confirmation'),
        forceFormData: true // Required for file uploads
    });
};


const countries = ["USA", "UK", "Canada", "Germany", "France"];
</script>

<template>
    <AuthBase title="Create an account" description="Enter your details below to create your account">
        <Head title="Register" />

        <form @submit.prevent="submit" class="flex flex-col gap-6">
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
                    <Label for="national_id">National ID</Label>
                    <Input id="national_id" type="text" required autofocus :tabindex="1" autocomplete="national_id" v-model="form.national_id" placeholder="##############" />
                    <InputError :message="form.errors.national_id" />
                </div>

                <div class="grid gap-2">
                    <Label for="mobile">Mobile Number</Label>
                    <Input id="mobile" type="text" required :tabindex="4" autocomplete="tel" v-model="form.mobile" placeholder="+1234567890" />
                    <InputError :message="form.errors.mobile" />
                </div>

                <div class="grid gap-2">
                    <Label for="country">Country</Label>
                    <Input id="country" type="text" required :tabindex="5" autocomplete="country-name" v-model="form.country" placeholder="Your country" />
                    <InputError :message="form.errors.country" />
                </div>

                <div class="grid gap-2">
                    <Label for="gender">Gender</Label>
                    <div class="flex gap-4 mt-1">
                        <div class="flex items-center">
                            <Input
                                id="gender-male"
                                type="radio"
                                name="gender"
                                value="male"
                                v-model="form.gender"
                                :tabindex="6"
                                class="h-4 w-4"
                            />
                            <Label for="gender-male" class="ml-2">Male</Label>
                        </div>
                        <div class="flex items-center">
                            <Input
                                id="gender-female"
                                type="radio"
                                name="gender"
                                value="female"
                                v-model="form.gender"
                                :tabindex="7"
                                class="h-4 w-4"
                            />
                            <Label for="gender-female" class="ml-2">Female</Label>
                        </div>
                    </div>
                    <InputError :message="form.errors.gender" />
                </div>

                <div class="grid gap-2">
                    <Label for="avatar_img">Profile Picture (Optional)</Label>
                    <Input
                        id="avatar_img"
                        type="file"
                        :tabindex="8"
                        @input="form.avatar_img = $event.target.files[0]"
                        accept="image/jpeg,image/jpg,image/png"
                        class="w-full rounded-lg border border-gray-600 p-2"
                    />
                    <InputError :message="form.errors.avatar_img" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="3"
                        autocomplete="new-password"
                        v-model="form.password"
                        placeholder="Password"
                    />
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

                <Button type="submit" class="mt-2 w-full" tabindex="11" :disabled="form.processing">
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
