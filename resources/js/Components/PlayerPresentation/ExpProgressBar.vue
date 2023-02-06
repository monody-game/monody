<template>
  <div
    class="progress-bar"
    :style="progressStyle"
  >
    <slot />
  </div>
</template>

<script setup>
import { useStore } from "../../stores/user";

const exp = useStore()["exp"];
const total_exp = 100;

const expPercentage = (exp * total_exp) / 100;
const progress = (expPercentage * 85) / 100;

const progressStyle = `background-image: conic-gradient(from 206deg at 50% 50%, var(--accent-primary) 0 ${progress}%, var(--primary) 0);`;
</script>
