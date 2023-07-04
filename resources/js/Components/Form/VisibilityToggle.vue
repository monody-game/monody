<template>
	<svg ref="svg">
		<use :href="'/sprite.svg#' + icon" />
	</svg>
</template>

<script setup>
import { onMounted, ref } from "vue";

const props = defineProps({
	field: String,
});
const svg = ref(null);
const icon = ref("closed_eye");

onMounted(() => {
	const field = document.querySelector(`#${props.field}`);
	svg.value.addEventListener("click", () => {
		if (field.type === "password") {
			field.type = "text";
			icon.value = "eye";
			return;
		}

		icon.value = "closed_eye";
		field.type = "password";
	});
});
</script>
