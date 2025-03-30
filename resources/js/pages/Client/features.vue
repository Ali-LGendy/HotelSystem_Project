<script setup>
import { Card, CardContent } from "@/components/ui/card";

const features = [
  {
    title: "Luxurious Accommodations",
    description: "Experience unparalleled comfort in our meticulously designed rooms and suites.",
    icon: "🏨"
  },
  {
    title: "Fine Dining",
    description: "Savor exquisite cuisine at our world-class restaurants and bars.",
    icon: "🍽️"
  },
  {
    title: "Spa & Wellness",
    description: "Rejuvenate your body and mind in our premium spa facilities.",
    icon: "💆"
  },
  {
    title: "24/7 Service",
    description: "Our dedicated staff is available around the clock to ensure your comfort.",
    icon: "⏰"
  },
  {
    title: "Event Spaces",
    description: "Perfect venues for your special occasions and business meetings.",
    icon: "🎉"
  },
  {
    title: "Prime Location",
    description: "Conveniently located with easy access to major attractions.",
    icon: "📍"
  }
];
</script>

<template>
  <section class="py-16 bg-white dark:bg-black">
    <div class="container mx-auto px-4">
      <div class="text-center mb-12">
        <h2 class="text-3xl font-bold mb-4 text-gray-900 dark:text-white">Why Choose Us</h2>
        <p class="text-gray-600 dark:text-gray-300">
          Experience unparalleled luxury and comfort
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <Card 
          v-for="feature in features" 
          :key="feature.title" 
          class="border-none shadow-none bg-white/50 dark:bg-black/50 backdrop-blur"
        >
          <CardContent class="pt-6">
            <div class="text-4xl mb-4">{{ feature.icon }}</div>
            <h3 class="text-xl font-semibold mb-2 text-gray-900 dark:text-white">{{ feature.title }}</h3>
            <p class="text-gray-600 dark:text-gray-300">{{ feature.description }}</p>
          </CardContent>
        </Card>
      </div>
    </div>
  </section>
</template>