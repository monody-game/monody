<template>
  <div class="time-separator__main">
    <p class="time-separator__message">{{ message }}</p>
  </div>
</template>

<script>
export default {
  name: "TimeSeparator",
  props: ["message"]
};
</script>