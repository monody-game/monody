<template>
  <div>
    <spinning-dots></spinning-dots>
  </div>
</template>

<script>
import SpinningDots from "@grafikart/spinning-dots-element";

customElements.define("spinning-dots", SpinningDots);
export default {
  name: "DotsSpinner",
};
</script>

<style scoped>

</style>