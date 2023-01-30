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
console.log(exp);
const progress = (exp * 100) / 360;
const progressStyle = `background-image: conic-gradient(from 0deg at 50% 50%, #FFF5CF 0 ${progress}%, #FFFCF1 0);`;
</script>
