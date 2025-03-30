<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
function useCurrentUser() {
    const page = usePage();
    return page.props.auth.user;
}

const user = useCurrentUser();
const props = defineProps({
  room: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['book-room']);

const statusColor = computed(() => {
  const colors = {
    available: 'success',
    occupied: 'destructive',
    maintenance: 'warning'
  };
  return colors[props.room.status] || 'secondary';
});

const handleBooking = () => {
  if(user){
  emit('book-room', props.room);
}else{
  router.visit('/login');
}
};
</script>

<template>
  <Card class="overflow-hidden bg-white dark:bg-black">
    <div class="relative aspect-video">
      <img :src="room.image" :alt="room.name" class="object-cover w-full h-full" />
      <div class="absolute top-2 right-2">
        <Badge :variant="room.status === 'available' ? 'default' : 'destructive'" class="text-white dark:text-black-200">
          {{ room.status }}
        </Badge>
      </div>
    </div>

    <CardHeader>
      <CardTitle class="flex justify-between items-center">
        <span>{{ room.name }}</span>
        <span class="text-lg">${{ room.price/100 }}/night</span>
      </CardTitle>
      <p class="text-sm text-muted-foreground">Room {{ room.room_number }}</p>
    </CardHeader>

    <CardContent>
      <div class="space-y-4">
        <div class="flex items-center gap-2">
          <!-- <Badge variant="outline">{{ room.type }}</Badge> -->
          <Badge variant="outline">{{ room.room_capacity }} Guests</Badge>
        </div>
        
        <p class="text-sm text-muted-foreground">{{ room.description }}</p>

        <div class="grid grid-cols-2 gap-2 text-sm">
          <div v-for="(feature, index) in room.features" :key="index" class="flex items-center gap-2">
            <svg class="w-4 h-4 text-primary" viewBox="0 0 24 24">
              <path fill="currentColor" d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
            </svg>
            <span>{{ feature }}</span>
          </div>
        </div>
      </div>
    </CardContent>

    <CardFooter>
      <Button 
        class="w-full" 
        :variant="room.status === 'available' ? 'default' : 'secondary'"
        :disabled="room.status !== 'available'"
        @click="handleBooking"
      >
        {{ room.status === 'available' ? 'Book Now' : 'Unavailable' }}
      </Button>
    </CardFooter>
  </Card>
</template>