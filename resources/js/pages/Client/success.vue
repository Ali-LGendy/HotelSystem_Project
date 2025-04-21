<script setup>
import { Button } from '@/components/ui/button'
import { Link, router } from '@inertiajs/vue3';

</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-900 text-white px-4">
    <div class="max-w-md w-full text-center space-y-6 bg-gray-800 p-8 rounded-xl shadow-xl border border-gray-700">
      <h1 class="text-3xl font-bold text-green-400">🎉 Payment Successful!</h1>
      <p class="text-lg text-gray-300">
        Thank you for completing your reservation. You can now view your reservation details.
      </p>
      <Link  :href="route('hotel.landing')">
        <Button class="w-full mt-4">
            Back to Home Page
        </Button>
      </Link>
    </div>
  </div>
</template>