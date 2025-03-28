<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  message: {
    type: String,
    required: true
  },
  description: {
    type: String,
    default: ''
  },
  type: {
    type: String,
    default: 'success', // success, error, warning, info
    validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
  },
  duration: {
    type: Number,
    default: 3000
  },
  position: {
    type: String,
    default: 'top-right',
    validator: (value) => ['top-right', 'top-left', 'bottom-right', 'bottom-left', 'top-center', 'bottom-center'].includes(value)
  }
});

const emit = defineEmits(['close']);
const isVisible = ref(false);
const timer = ref(null);

const getPositionClasses = () => {
  switch (props.position) {
    case 'top-right':
      return 'top-4 right-4';
    case 'top-left':
      return 'top-4 left-4';
    case 'bottom-right':
      return 'bottom-4 right-4';
    case 'bottom-left':
      return 'bottom-4 left-4';
    case 'top-center':
      return 'top-4 left-1/2 -translate-x-1/2';
    case 'bottom-center':
      return 'bottom-4 left-1/2 -translate-x-1/2';
    default:
      return 'top-4 right-4';
  }
};

const getTypeClasses = () => {
  switch (props.type) {
    case 'success':
      return 'bg-green-600 text-white';
    case 'error':
      return 'bg-red-600 text-white';
    case 'warning':
      return 'bg-yellow-600 text-white';
    case 'info':
      return 'bg-blue-600 text-white';
    default:
      return 'bg-green-600 text-white';
  }
};

const close = () => {
  isVisible.value = false;
  setTimeout(() => {
    emit('close');
  }, 300); // Wait for fade out animation
};

onMounted(() => {
  // Show toast with animation
  setTimeout(() => {
    isVisible.value = true;
  }, 10);
  
  // Auto close after duration
  timer.value = setTimeout(() => {
    close();
  }, props.duration);
});

onUnmounted(() => {
  if (timer.value) {
    clearTimeout(timer.value);
  }
});
</script>

<template>
  <div 
    :class="[
      'fixed z-50 min-w-[350px] max-w-md p-4 rounded-md shadow-lg transform transition-all duration-300',
      getPositionClasses(),
      getTypeClasses(),
      isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-2'
    ]"
  >
    <div class="flex justify-between items-start">
      <div class="flex-1">
        <div class="font-medium">{{ message }}</div>
        <div v-if="description" class="mt-1 text-sm opacity-90">{{ description }}</div>
      </div>
      <button
        @click="close"
        class="ml-4 text-white hover:text-gray-200 focus:outline-none"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
      </button>
    </div>
  </div>
</template>