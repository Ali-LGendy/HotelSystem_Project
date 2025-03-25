<script setup>
import { cn } from '@/lib/utils';
import { useVModel } from '@vueuse/core';

const props = defineProps({
    modelValue: String, // The selected country
    class: String,
});

const emits = defineEmits(['update:modelValue']);

const modelValue = useVModel(props, 'modelValue', emits);
</script>

<template>
    <select
        v-model="modelValue"
        :class="cn(
            'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
            props.class
        )"
    >
        <slot></slot>
    </select>
</template>
