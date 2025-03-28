import { inject } from 'vue';

// Create a simple alert-based fallback toast implementation
const createFallbackToast = () => {
  return {
    show: (message, options) => {
      const description = options?.description ? `\n${options.description}` : '';
      alert(`${message}${description}`);
    },
    success: (message, options) => {
      const description = options?.description ? `\n${options.description}` : '';
      alert(`Success: ${message}${description}`);
    },
    error: (message, options) => {
      const description = options?.description ? `\n${options.description}` : '';
      alert(`Error: ${message}${description}`);
    },
    warning: (message, options) => {
      const description = options?.description ? `\n${options.description}` : '';
      alert(`Warning: ${message}${description}`);
    },
    info: (message, options) => {
      const description = options?.description ? `\n${options.description}` : '';
      alert(`Info: ${message}${description}`);
    },
  };
};

// Singleton instance of the fallback toast
let fallbackToast = null;

export function useToast() {
  // Try to inject the toast service
  const toast = inject('toast', null);

  // If toast service is not available, use the fallback
  if (!toast) {
    // Create the fallback toast if it doesn't exist yet
    if (!fallbackToast) {
      fallbackToast = createFallbackToast();
    }
    return fallbackToast;
  }

  // Return the injected toast service
  return toast;
}