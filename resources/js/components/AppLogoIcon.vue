<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <!-- <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 42" :class="className" v-bind="$attrs">
        <path
            fill="currentColor"
            fill-rule="evenodd"
            clip-rule="evenodd"
            d="M17.2 5.633 8.6.855 0 5.633v26.51l16.2 9 16.2-9v-8.442l7.6-4.223V9.856l-8.6-4.777-8.6 4.777V18.3l-5.6 3.111V5.633ZM38 18.301l-5.6 3.11v-6.157l5.6-3.11V18.3Zm-1.06-7.856-5.54 3.078-5.54-3.079 5.54-3.078 5.54 3.079ZM24.8 18.3v-6.157l5.6 3.111v6.158L24.8 18.3Zm-1 1.732 5.54 3.078-13.14 7.302-5.54-3.078 13.14-7.3v-.002Zm-16.2 7.89 7.6 4.222V38.3L2 30.966V7.92l5.6 3.111v16.892ZM8.6 9.3 3.06 6.222 8.6 3.143l5.54 3.08L8.6 9.3Zm21.8 15.51-13.2 7.334V38.3l13.2-7.334v-6.156ZM9.6 11.034l5.6-3.11v14.6l-5.6 3.11v-14.6Z"
        />
    </svg> -->
    <!-- <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 42" :class="className" v-bind="$attrs">
  <path
    fill="currentColor"
    fill-rule="evenodd"
    stroke="currentColor"
    stroke-width="0.3"
    clip-rule="evenodd"
    d="M20 0L5 8v26l15 8 15-8V8L20 0zm-3 8l-9 5v16l9 5V8zm6 26l9-5V13l-9-5v26zm-9-23v16l9 5V16l-9-5zm9-5l9 5-9 5-9-5 9-5zM8 14l-2 1v12l2-1V14zm12 18l-8 4v-4l8-4v4zm10-14l-6 3v5l6-3v-5zM30 9l-6 3 6 3 6-3-6-3zM20 39v-8l10-6v8l-10 6z"
  />
</svg> -->
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 42" :class="className" v-bind="$attrs">
        <path
            fill="currentColor"
            fill-rule="evenodd"
            clip-rule="evenodd"
            d="M20 2.633L6 10.855v20.51l14 8 14-8v-20.51L20 2.633ZM6 10.855l14-8 14 8-14 8-14-8ZM8 13.633v16l10 5.714V19.347L8 13.633ZM32 13.633l-10 5.714v16l10-5.714v-16ZM20 20.855l-10-5.714 10-5.714 10 5.714-10 5.714ZM18 15.855l-6-3.428V22l6 3.428V15.855ZM22 15.855l6-3.428V22l-6 3.428V15.855ZM12 24.855l6 3.428v8l-6-3.428v-8ZM28 24.855l-6 3.428v8l6-3.428v-8Z"
        />
    </svg>
</template>
