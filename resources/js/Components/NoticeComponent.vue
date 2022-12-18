<template>
  <svg
    ref="icon"
    class="notice__icon"
  >
    <use href="/sprite.svg#question" />
  </svg>
  <div
    ref="content"
    class="notice__content"
  >
    <p class="notice__title">
      {{ props.title }}
    </p>
    <p class="notice__description">
      <slot />
    </p>
  </div>
</template>

<script setup>
import { onMounted, ref } from "vue";

const props = defineProps({
	title: String
});
const icon = ref(null);
const content = ref(null);

onMounted(() => {
	icon.value.addEventListener("pointerenter", () => {
		content.value.style.display = "grid";
	});

	icon.value.addEventListener("pointerleave", () => {
		content.value.style.display = "none";
	});
});
</script>
