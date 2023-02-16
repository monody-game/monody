<template>
  <div
    class="progress-bar"
    :style="`background-image: conic-gradient(from 201deg at 50% 50%, var(--accent-primary) 0 ${progress}%, var(--primary) 0);`"
  >
    <slot />
  </div>
</template>

<script setup>
import { useStore } from "../../stores/user";
import { computed } from "vue";

const store = useStore();

const expPercentage = computed(() => (store.exp * 100) / store.exp_needed);
const progress = computed(() => (expPercentage.value * 88) / 100);
</script>
