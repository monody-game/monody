<template>
	<div class="time-separator__main">
		<p class="time-separator__message">
			{{ props.message }}
		</p>
	</div>
</template>

<script setup>
const props = defineProps({
	message: {
		type: String,
		required: true,
	},
});
</script>
