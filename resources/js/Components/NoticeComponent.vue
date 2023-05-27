<template>
  <div
    class="notice-component__wrapper"
    @mouseover="isShown = true"
    @mouseout="isShown = false"
  >
    <svg
      class="notice__icon"
    >
      <use href="/sprite.svg#question" />
    </svg>
    <div
      :style="isShown ? 'display: grid;' : 'display: none;'"
      class="notice__content"
    >
      <p class="notice__title">
        {{ props.title }}
      </p>
      <p class="notice__description">
        <slot />
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";

const props = defineProps({
	title: String,
	shown: {
		type: Boolean,
		required: false,
		default: false
	}
});

const isShown = ref(props.shown);

watch(props, (value) => {
	isShown.value = value.shown;
});

</script>
