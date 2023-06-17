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
      :style="'right:' + (100 - (currentPage / pages) * 100) + '%'"
    />
    <div class="auth-page__title-group">
      <h1>
        <slot
          name="title"
          :page="currentPage"
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
        :page="currentPage"
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
  </div>
</template>

<script setup>
import { computed, ref } from "vue";
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
	}
});

const emit = defineEmits(["submit", "currentPage"]);

const submitContent = computed(() => {
	switch (currentPage.value) {
	default:
		return "Suivant";
	case 2:
		return "Passer";
	case 3:
		return "Terminer";
	}
});
const currentPage = ref(1);

const next = () => {
	currentPage.value === Number.parseInt(props.pages) ? emit("submit") : currentPage.value++;
	emit("currentPage", currentPage.value);
};
</script>
