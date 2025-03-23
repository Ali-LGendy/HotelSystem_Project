<template>
  <div v-if="open" class="fixed inset-0 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6">
      <DialogHeader>
        <slot name="header"></slot>
      </DialogHeader>
      <DialogContent>
        <slot name="content"></slot>
      </DialogContent>
      <div class="mt-4 flex justify-end space-x-2">
        <Button variant="outline" @click="close">
          Cancel
        </Button>
        <Button variant="destructive" @click="$emit('confirm')">
          Confirm
        </Button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
  open: {
    type: Boolean,
    required: true
  }
});

const close = () => {
  props.open = false;
};
</script>

<style scoped>
/* Add any necessary styles here */
</style>
