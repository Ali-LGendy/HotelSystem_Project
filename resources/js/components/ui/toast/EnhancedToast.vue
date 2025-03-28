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
    default: 5000
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
      return 'bg-green-50 border-green-500 text-green-800';
    case 'error':
      return 'bg-red-50 border-red-500 text-red-800';
    case 'warning':
      return 'bg-yellow-50 border-yellow-500 text-yellow-800';
    case 'info':
      return 'bg-blue-50 border-blue-500 text-blue-800';
    default:
      return 'bg-green-50 border-green-500 text-green-800';
  }
};

const getIconClasses = () => {
  switch (props.type) {
    case 'success':
      return 'text-green-500';
    case 'error':
      return 'text-red-500';
    case 'warning':
      return 'text-yellow-500';
    case 'info':
      return 'text-blue-500';
    default:
      return 'text-green-500';
  }
};

const getIcon = () => {
  switch (props.type) {
    case 'success':
      return `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>`;
    case 'error':
      return `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>`;
    case 'warning':
      return `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>`;
    case 'info':
      return `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>`;
    default:
      return `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>`;
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
      'fixed z-50 min-w-[350px] max-w-md p-4 rounded-md shadow-lg transform transition-all duration-300 border-l-4',
      getPositionClasses(),
      getTypeClasses(),
      isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-2'
    ]"
  >
    <div class="flex items-start">
      <div :class="['flex-shrink-0 mr-3', getIconClasses()]" v-html="getIcon()"></div>
      <div class="flex-1">
        <h3 class="text-sm font-medium">{{ message }}</h3>
        <div v-if="description" class="mt-1 text-sm opacity-90">
          {{ description }}
        </div>
      </div>
      <button 
        @click="close" 
        :class="['ml-4 flex-shrink-0 inline-flex rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2', 
                 props.type === 'success' ? 'focus:ring-green-500' : 
                 props.type === 'error' ? 'focus:ring-red-500' :
                 props.type === 'warning' ? 'focus:ring-yellow-500' : 'focus:ring-blue-500']"
      >
        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
      </button>
    </div>
    <div class="absolute bottom-0 left-0 right-0 h-1 bg-gray-200 rounded-b-md overflow-hidden">
      <div 
        :class="[
          'h-full transition-all duration-linear',
          props.type === 'success' ? 'bg-green-500' : 
          props.type === 'error' ? 'bg-red-500' :
          props.type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'
        ]"
        :style="{ width: '100%', animation: `shrink ${props.duration}ms linear forwards` }"
      ></div>
    </div>
  </div>
</template>

<style scoped>
@keyframes shrink {
  from { width: 100%; }
  to { width: 0%; }
}
</style>