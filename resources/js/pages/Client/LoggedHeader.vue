<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Button } from "@/components/ui/button";
import {
  NavigationMenu,
  NavigationMenuContent,
  NavigationMenuItem,
  NavigationMenuLink,
  NavigationMenuList,
  NavigationMenuTrigger,
} from "@/components/ui/navigation-menu";
import AppLogoIcon from '@/components/AppLogoIcon.vue';

const isMenuOpen = ref(false);

const navigationItems = [
  { label: 'Home', href: '/' },
  { label: 'Rooms', href: '/rooms' },
  { label: 'Services', href: '/services' },
  { label: 'About', href: '/about' },
  { label: 'Contact', href: '/contact' },
];
</script>

<template>
  <header class="sticky top-0 z-50 w-full border-b bg-white dark:bg-black backdrop-blur supports-[backdrop-filter]:bg-white/60 dark:supports-[backdrop-filter]:bg-black/60">
    <div class="container flex h-16 items-center">
      <div class="mx-5 px-5 flex">
        <Link href="/" class="mr-6 flex items-center space-x-2">
          <AppLogoIcon class="size-9 fill-current text-black dark:text-white" />
          <span class="text-xl font-semibold text-black-900 dark:text-white">Four Seasons</span>
        </Link>
      </div>

      <!-- Desktop Navigation -->
      <NavigationMenu class="hidden md:flex">
        <NavigationMenuList>
          <NavigationMenuItem v-for="item in navigationItems" :key="item.label">
            <NavigationMenuLink 
              :href="item.href" 
              class="group inline-flex h-10 w-max items-center justify-center rounded-md bg-white dark:bg-black px-4 py-2 text-sm font-medium text-gray-900 dark:text-white transition-colors hover:bg-gray-100 dark:hover:bg-gray-900"
            >
              {{ item.label }}
            </NavigationMenuLink>
          </NavigationMenuItem>
        </NavigationMenuList>
      </NavigationMenu>

      <!-- Mobile Navigation Button -->
      <Button
        variant="ghost"
        class="md:hidden text-gray-900 dark:text-white"
        @click="isMenuOpen = !isMenuOpen"
      >
        <span class="sr-only">Toggle menu</span>
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
          :class="{ 'hidden': isMenuOpen }"
        >
          <line x1="3" y1="12" x2="21" y2="12" />
          <line x1="3" y1="6" x2="21" y2="6" />
          <line x1="3" y1="18" x2="21" y2="18" />
        </svg>
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
          :class="{ 'hidden': !isMenuOpen }"
        >
          <line x1="18" y1="6" x2="6" y2="18" />
          <line x1="6" y1="6" x2="18" y2="18" />
        </svg>
      </Button>

      <div class="flex flex-1 items-center justify-end space-x-4">
        <nav class="flex items-center space-x-2">
          <Button asChild variant="outline" class="text-gray-900 dark:text-white">
            <Link :href="route('clients.myreservation')">My Reservations</Link>
          </Button>
          <Button asChild variant="outline" class="text-gray-900 dark:text-white">
            <Link href="/profile">Profile</Link>
          </Button>
          <Button asChild variant="destructive">
            <Link :href="route('logout')" method="post" as="button">Logout</Link>
          </Button>
        </nav>
      </div>
    </div>

    <!-- Mobile Navigation Menu -->
    <div
      v-show="isMenuOpen"
      class="md:hidden bg-white dark:bg-black"
    >
      <div class="space-y-1 px-4 pb-3 pt-2">
        <Link
          v-for="item in navigationItems"
          :key="item.label"
          :href="item.href"
          class="block rounded-md px-3 py-2 text-base font-medium text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-900"
          @click="isMenuOpen = false"
        >
          {{ item.label }}
        </Link>
      </div>
    </div>
  </header>
</template>