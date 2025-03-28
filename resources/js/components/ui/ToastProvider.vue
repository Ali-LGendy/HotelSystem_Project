<template>
  <div class="toast-container">
    <div v-for="(toast, index) in toasts" :key="index" 
         :class="['toast', `toast-${toast.type}`]"
         @click="removeToast(index)">
      <div class="toast-header">
        <strong>{{ toast.title }}</strong>
        <button type="button" class="close" @click.stop="removeToast(index)">&times;</button>
      </div>
      <div class="toast-body" v-if="toast.message">
        {{ toast.message }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, provide } from 'vue';

const toasts = ref([]);

const addToast = (title, message = '', type = 'default', options = {}) => {
  const toast = {
    title,
    message,
    type,
    ...options
  };
  
  toasts.value.push(toast);
  
  // Auto-remove toast after duration
  const duration = options.duration || 5000;
  setTimeout(() => {
    const index = toasts.value.indexOf(toast);
    if (index !== -1) {
      toasts.value.splice(index, 1);
    }
  }, duration);
};

const removeToast = (index) => {
  toasts.value.splice(index, 1);
};

// Create toast service
const toast = {
  show: (title, options) => addToast(title, options?.description, 'default', options),
  success: (title, options) => addToast(title, options?.description, 'success', options),
  error: (title, options) => addToast(title, options?.description, 'error', options),
  warning: (title, options) => addToast(title, options?.description, 'warning', options),
  info: (title, options) => addToast(title, options?.description, 'info', options),
};

// Provide toast service to all components
provide('toast', toast);
</script>

<style scoped>
.toast-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-width: 350px;
}

.toast {
  background-color: #fff;
  border-radius: 4px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  padding: 12px 16px;
  transition: all 0.3s ease;
  cursor: pointer;
}

.toast-success {
  border-left: 4px solid #10b981;
}

.toast-error {
  border-left: 4px solid #ef4444;
}

.toast-warning {
  border-left: 4px solid #f59e0b;
}

.toast-info {
  border-left: 4px solid #3b82f6;
}

.toast-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}

.toast-body {
  color: #6b7280;
  font-size: 0.875rem;
}

.close {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.25rem;
  color: #9ca3af;
}
</style>