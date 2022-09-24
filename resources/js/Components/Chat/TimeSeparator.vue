<template>
  <p class="time-separator__message">
    {{ props.message }}
  </p>
</template>

<script setup>
const props = defineProps({
	message: {
		type: String,
		required: true
	}
});
</script>
