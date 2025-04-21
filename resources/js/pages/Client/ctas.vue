<script setup>
import { Link } from '@inertiajs/vue3';
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";

const ctaCards = [
  {
    title: "Special Offers",
    description: "Discover our exclusive packages and seasonal promotions for an unforgettable stay.",
    image: "storage/assets/special-offers.jpg",
    link: "/offers"
  },
  {
    title: "Events & Meetings",
    description: "Perfect venues for your corporate events, weddings, and special occasions.",
    image: "storage/assets/events.jpg",
    link: "/events"
  },
  {
    title: "Dining Experience",
    description: "Explore our world-class restaurants and unique culinary experiences.",
    image: "storage/assets/dining.jpg",
    link: "/dining"
  }
];

const scrollToRooms = (event) => {
  event.preventDefault();
  const targetSection = document.getElementById('rooms');
  if (targetSection) {
    targetSection.scrollIntoView({
      behavior: 'smooth',
      block: 'start',
    });
    window.history.pushState(null, null, '#rooms');
  }
};
</script>

<template>
  <section class="relative overflow-hidden bg-white dark:bg-black">
    <div class="relative h-screen w-full">
  <!-- Dark overlay gradient -->
  <div class="absolute  inset-0 bg-gradient-to-b from-black/70 via-black/50 to-black/30 dark:from-black/80 dark:via-black/60 dark:to-black/40 z-10"></div>
  
  <!-- Background image div (positioned absolutely to fill) -->
  <div class="absolute py-5 inset-0 bg-cover bg-center" style="background-image: url('storage/assets/hotelbackgroun.png');"></div>
  
  <!-- Content container -->
  <div class="relative z-20 md:p-12 lg:p-16 max-w-4xl mx-auto text-center h-full flex flex-col justify-center items-center">
    <h2 class="text-3xl sm:text-4xl md:text-6xl lg:text-7xl font-extrabold text-white dark:text-white leading-tight mb-6 animate-fade-in-down">
      Welcome to Our Luxury Hotel
    </h2>

    <p class="text-lg sm:text-xl md:text-2xl text-white/90 mb-10 animate-in fade-in slide-in-from-bottom-4 duration-1000 delay-200">
      Experience comfort and elegance in every stay
    </p>

    <div class="mt-6 sm:mt-10">
      <Button 
        size="lg"
        variant="secondary"
        class="animate-in fade-in zoom-in duration-1000 delay-300"
        @click="scrollToRooms"
      >
        Book Your Stay Today
      </Button>
    </div>
  </div>
</div>
    <div class="container mx-auto px-4 mt-8">
      <div class="text-center mb-12">
        <h2 class="text-3xl font-bold mb-4 text-gray-900 dark:text-white">Experience Luxury</h2>
        <p class="text-gray-600 dark:text-gray-300">
          Indulge in exceptional experiences tailored just for you
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <Card 
          v-for="cta in ctaCards" 
          :key="cta.title"
          class="overflow-hidden group hover:shadow-lg transition-shadow duration-300 bg-white dark:bg-black"
        >
          <div class="relative aspect-video">
            <img 
              :src="cta.image" 
              :alt="cta.title"
              class="object-cover w-full h-full transition-transform duration-300 group-hover:scale-105"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
          </div>
          <CardContent class="p-6">
            <h3 class="text-xl font-semibold mb-2 text-gray-900 dark:text-white">{{ cta.title }}</h3>
            <p class="text-gray-600 dark:text-gray-300 mb-4">{{ cta.description }}</p>
            <Button asChild variant="outline" class="w-full">
              <Link :href="cta.link">Learn More</Link>
            </Button>
          </CardContent>
        </Card>
      </div>
    </div>
  </section>
</template>

<style scoped>
@keyframes fade-in-down {
  0% {
    opacity: 0;
    transform: translateY(-20px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fade-in-up {
  0% {
    opacity: 0;
    transform: translateY(20px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes bounce-in {
  0% {
    opacity: 0;
    transform: scale(0.8);
  }
  50% {
    transform: scale(1.05);
  }
  100% {
    opacity: 1;
    transform: scale(1);
  }
}

.animate-fade-in-down {
  animation: fade-in-down 1s ease-out forwards;
}

.animate-fade-in-up {
  animation: fade-in-up 1s ease-out forwards;
}

.animate-bounce-in {
  animation: bounce-in 1s ease-out forwards;
}

.delay-200 {
  animation-delay: 0.2s;
}

.delay-400 {
  animation-delay: 0.4s;
}

#rooms {
  scroll-margin-top: 80px;
}
</style>