<template>
  <div
    class="notice-component__wrapper"
    @mouseover="content.style.display = 'grid'"
    @mouseout="content.style.display = 'none'"
  >
    <svg
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
  </div>
</template>

<script setup>
import { ref } from "vue";

const props = defineProps({
	title: String
});
const content = ref(null);
</script>
