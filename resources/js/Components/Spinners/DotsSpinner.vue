<template>
  <div class="spinner__dots-container">
    <spinning-dots style="width:24px;" />
  </div>
</template>
<script setup>
import SpinningDots from "@grafikart/spinning-dots-element";

customElements.define("spinning-dots", SpinningDots);
</script>
