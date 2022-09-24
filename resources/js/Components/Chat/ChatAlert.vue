<template>
  <svg class="alert-message__icon">
    <use :href="'/sprite.svg#' + props.type" />
  </svg>
  <p class="alert-message__text">
    {{ props.message }}
  </p>
</template>

<script setup>
const props = defineProps({
	message: {
		type: String,
		required: true
	},
	type: {
		type: String,
		required: true
	}
});
</script>
