<script setup>
import { ref, provide } from 'vue';
import Toast from './Toast.vue';

const toasts = ref([]);
let toastId = 0;

const addToast = (message, options = {}) => {
  const id = toastId++;
  const toast = {
    id,
    message,
    description: options.description || '',
    type: options.type || 'success',
    duration: options.duration || 3000,
    position: options.position || 'top-right'
  };

  toasts.value.push(toast);
  return id;
};

const removeToast = (id) => {
  const index = toasts.value.findIndex(toast => toast.id === id);
  if (index !== -1) {
    toasts.value.splice(index, 1);
  }
};

// Create convenience methods for different toast types
const success = (message, options = {}) => addToast(message, { ...options, type: 'success' });
const error = (message, options = {}) => addToast(message, { ...options, type: 'error' });
const warning = (message, options = {}) => addToast(message, { ...options, type: 'warning' });
const info = (message, options = {}) => addToast(message, { ...options, type: 'info' });

// Provide toast methods to all components
provide('toast', {
  show: addToast,
  success,
  error,
  warning,
  info,
  remove: removeToast
});

// Export for direct import
defineExpose({
  show: addToast,
  success,
  error,
  warning,
  info,
  remove: removeToast
});
</script>

<template>
  <div>
    <Toast
      v-for="toast in toasts"
      :key="toast.id"
      :message="toast.message"
      :description="toast.description"
      :type="toast.type"
      :duration="toast.duration"
      :position="toast.position"
      @close="removeToast(toast.id)"
    />
  </div>
</template>