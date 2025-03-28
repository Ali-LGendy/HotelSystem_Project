<script setup>
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { router, useForm } from '@inertiajs/vue3';

defineOptions({ layout: AppLayout });

const props = defineProps({
  countries: {
    type: Array,
    default: () => []
  }
});

const form = useForm({
  name: '',
  email: '',
  password: '',
  gender: 'male',
  mobile: '',
  country: '',
  avatar_image: null,
});

const handleFileUpload = (event) => {
  form.avatar_image = event.target.files[0];
};

const submitForm = () => {
  form.post('/receptionist/clients', {
    onSuccess: () => {
      router.visit('/receptionist/clients', {
        preserveScroll: true,
        preserveState: true,
        replace: true
      });
    },
    onError: (errors) => {
      console.error('Form submission errors:', errors);
    },
    preserveScroll: true
  });
};
</script>

<template>
  <Card class="mx-auto max-w-2xl rounded-lg bg-white shadow-lg dark:bg-gray-900">
    <CardHeader>
      <CardTitle class="text-gray-900 dark:text-gray-100">Create New Client</CardTitle>
    </CardHeader>
    <CardContent>
      <form @submit.prevent="submitForm" enctype="multipart/form-data" class="space-y-6">
        <!-- Name -->
        <div>
          <Label for="name" class="dark:text-gray-300">Name</Label>
          <Input v-model="form.name" id="name" placeholder="Enter client's name" required />
          <div v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</div>
        </div>

        <!-- Email -->
        <div>
          <Label for="email" class="dark:text-gray-300">Email</Label>
          <Input v-model="form.email" id="email" type="email" placeholder="Enter client's email" required />
          <div v-if="form.errors.email" class="mt-1 text-sm text-red-500">{{ form.errors.email }}</div>
        </div>

        <!-- Password -->
        <div>
          <Label for="password" class="dark:text-gray-300">Password</Label>
          <Input v-model="form.password" id="password" type="password" placeholder="Set a password" required />
          <div v-if="form.errors.password" class="mt-1 text-sm text-red-500">{{ form.errors.password }}</div>
        </div>

        <!-- Gender -->
        <div>
          <Label class="dark:text-gray-300">Gender</Label>
          <div class="mt-2 flex space-x-4">
            <div class="flex items-center space-x-2">
              <input type="radio" id="male" value="male" v-model="form.gender" class="h-4 w-4 text-primary focus:ring-primary" />
              <Label for="male" class="dark:text-gray-300">Male</Label>
            </div>
            <div class="flex items-center space-x-2">
              <input type="radio" id="female" value="female" v-model="form.gender" class="h-4 w-4 text-primary focus:ring-primary" />
              <Label for="female" class="dark:text-gray-300">Female</Label>
            </div>
          </div>
          <div v-if="form.errors.gender" class="mt-1 text-sm text-red-500">{{ form.errors.gender }}</div>
        </div>

        <!-- Mobile -->
        <div>
          <Label for="mobile" class="dark:text-gray-300">Mobile Number</Label>
          <Input v-model="form.mobile" id="mobile" placeholder="Enter mobile number" required />
          <div v-if="form.errors.mobile" class="mt-1 text-sm text-red-500">{{ form.errors.mobile }}</div>
        </div>

        <!-- Country -->
        <div>
          <Label for="country" class="dark:text-gray-300">Country</Label>
          <select
            v-model="form.country"
            id="country"
            class="mt-1 block w-full rounded-lg border border-gray-300 p-2 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200"
            required
          >
            <option value="" disabled>Select a country</option>
            <option v-for="country in countries" :key="country.code" :value="country.name">
              {{ country.name }}
            </option>
          </select>
          <div v-if="form.errors.country" class="mt-1 text-sm text-red-500">{{ form.errors.country }}</div>
        </div>

        <!-- Avatar Image -->
        <div>
          <Label for="avatar_image" class="dark:text-gray-300">Avatar Image</Label>
          <Input id="avatar_image" type="file" @change="handleFileUpload" accept="image/*" />
          <div v-if="form.errors.avatar_image" class="mt-1 text-sm text-red-500">{{ form.errors.avatar_image }}</div>
        </div>

        <!-- Submit Button -->
        <Button type="submit" class="w-full" :disabled="form.processing">
          {{ form.processing ? 'Creating...' : 'Create Client' }}
        </Button>
      </form>
    </CardContent>
  </Card>
</template>

<style scoped>
label {
  color: #4a4a4a;
}
</style>
