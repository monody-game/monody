<template>
  <div class="auth-page__form-wrapper">
    <div
      v-if="props.loading"
      class="auth-page__loading-group"
    >
      <div class="auth-page__loading-group-blur" />
      <DotsSpinner />
    </div>
    <span
      class="steps-form__advancement"
      :style="'right:' + (100 - (current / pages) * 100) + '%'"
    />
    <div class="auth-page__title-group">
      <h1>
        <slot
          name="title"
          :page="current"
          :total-page="props.pages"
        />
      </h1>
    </div>
    <form
      class="register-page__form"
      method="post"
      action=""
      @submit.prevent
    >
      <slot
        name="inputs"
        :page="current"
      />
      <div class="auth-page__submit-group">
        <slot
          name="submit"
        />
        <button
          class="btn large"
          type="submit"
          :disabled="disabled"
          @click="next"
        >
          {{ submitContent }}
        </button>
      </div>
    </form>
    <slot name="restriction" />
  </div>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import DotsSpinner from "../Spinners/DotsSpinner.vue";

const props = defineProps({
	loading: {
		type: Boolean,
		default: false
	},
	pages: {
		type: String,
		required: true
	},
	disabled: {
		type: Boolean,
		default: false
	},
	currentPage: {
		type: Number,
		default: 1
	}
});

const emit = defineEmits(["submit", "currentPage"]);
const current = ref(props.currentPage);

watch(props, (value) => {
	current.value = value.currentPage;
});

const submitContent = computed(() => {
	switch (current.value) {
	default:
		return "Suivant";
	case 2:
		return "Passer";
	case 3:
		return "Terminer";
	}
});

const next = () => {
	current.value === Number.parseInt(props.pages) ? emit("submit") : current.value++;
	emit("currentPage", current.value);
};
</script>
