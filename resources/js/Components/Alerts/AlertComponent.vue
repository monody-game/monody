<template>
  <div
    ref="alert"
    class="alert__container"
    :data-alert-type="props.type"
  >
    <svg class="alert__icon">
      <use :href="'/sprite.svg#' + props.type" />
    </svg>
    <p class="alert__content">
      {{ props.content }}
    </p>
    <svg
      class="alert__close"
      @click="close"
    >
      <use href="/sprite.svg#cross" />
    </svg>
    <span
      class="alert__progress"
      :class="'alert__progress-' + props.type"
    />
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useStore } from "../../stores/alerts.js";

const props = defineProps({
	type: String,
	content: String,
	id: String
});

const store = useStore();
const alert = ref(null);

const close = () => {
	clearTimeout(timeout);
	alert.value.classList.add("alert__out");
	store.dropAlert(props.id);
};

const timeout = setTimeout(close, 5000);
</script>
