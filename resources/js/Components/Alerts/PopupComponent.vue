<template>
  <BaseModal
    wrapper="popup__wrapper"
    :data-popup-type="store.type"
  >
    <header class="popup__header">
      <div class="popup__header-left">
        <svg
          class="popup__icon"
          role="button"
        >
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
        tabindex="0"
        @click="store.close()"
        @keydown.enter="store.close()"
        @keydown.space="store.close()"
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
      Note : {{ store.note }} <router-link
        v-if="store.link"
        :to="store.link"
        @click="store.close()"
      >
        {{ store.link_text }}
      </router-link>
    </p>
  </BaseModal>
</template>

<script setup>
import BaseModal from "../Modal/BaseModal.vue";
import { useStore } from "../../stores/modals/popup.js";
import { computed } from "vue";
const store = useStore();

const title = computed(() => {
	if (typeof store.title !== "undefined" && store.title.length > 0) return store.title;

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
