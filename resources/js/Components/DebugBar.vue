<template>
  <div
    class="debug-bar__wrapper"
    :data-openned="barOpenned"
  >
    <svg>
      <use href="/sprite.svg#beta" />
    </svg>
  </div>
</template>

<script setup>
import { ref } from "vue";

const barOpenned = ref(false);
</script>
