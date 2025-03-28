import { inject } from 'vue';

export function useToast() {
  const toast = inject('toast');
  
  if (!toast) {
    // Fallback if toast is not provided
    return {
      show: (message, options) => console.log('Toast not available:', message, options),
      success: (message, options) => console.log('Success toast not available:', message, options),
      error: (message, options) => console.log('Error toast not available:', message, options),
      warning: (message, options) => console.log('Warning toast not available:', message, options),
      info: (message, options) => console.log('Info toast not available:', message, options),
    };
  }
  
  return toast;
}