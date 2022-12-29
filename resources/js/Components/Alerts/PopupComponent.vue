<template>
  <BaseModal wrapper="popup__wrapper">
    <header class="popup__header">
      <div class="popup__header-left">
        <svg class="popup__icon">
          <use :href="'/sprite.svg#' + store.type" />
        </svg>
        <p
          id="modal__title"
          class="popup__title"
        >
          {{ title }}
        </p>
      </div>
      <svg
        class="popup__close"
        @click="store.close()"
      >
        <use href="/sprite.svg#cross" />
      </svg>
    </header>
    <p class="popup__content">
      {{ store.content }}
    </p>
    <p
      v-if="store.note !== ''"
      class="popup__note"
    >
      Note : {{ store.note }} <a
        v-if="store.link"
        :href="store.link"
      >{{ store.link_text }}</a>
    </p>
  </BaseModal>
</template>

<script setup>
import BaseModal from "../Modal/BaseModal.vue";
import { useStore } from "../../stores/popup.js";
import { computed } from "vue";
const store = useStore();

const title = computed(() => {
	switch (store.type) {
	case "success":
		return "Succès !";
	case "info":
		return "Information";
	case "warn":
		return "Attention";
	case "error":
		return "Erreur";
	default:
		return "Erreur";
	}
});
</script>
