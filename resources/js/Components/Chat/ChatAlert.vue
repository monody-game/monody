<template>
  <svg class="alert-message__icon">
    <use :href="'/sprite.svg#' + type" />
  </svg>
  <p class="alert-message__text">
    {{ message }}
  </p>
</template>

<script>
export default {
	name: "ChatAlert",
	props: {
		type: String,
		message: String
	}
};
</script>
