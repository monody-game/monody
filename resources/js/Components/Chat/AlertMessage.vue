<template>
  <svg class="alert-message__icon">
    <use :href="'/sprite.svg#' + type"></use>
  </svg>
  <p class="alert-message__text">{{ message }}</p>
</template>

<script>
export default {
  name: "AlertMessage",
  props: ['type', 'message']
}
</script>

<style scoped>

</style>
