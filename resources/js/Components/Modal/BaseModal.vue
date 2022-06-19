<template>
  <div
    class="modal__background"
    @click="closeModal()"
  >
    <div
      v-if="store.isOpenned"
      ref="modal"
      aria-modal="true"
      role="dialog"
      class="modal__main"
      tabindex="-1"
      aria-labelledby="modal__title"
      @click.stop=""
      @keyup.esc="closeModal()"
    >
      <slot />
    </div>
  </div>
</template>

<script>
import { useStore } from "../../stores/modal";
import { useModal } from "../../composables/modal";

export default {
	name: "BaseModal",
	data() {
		return {
			store: useStore()
		};
	},
	mounted() {
		this.$refs.modal.focus();
	},
	methods: {
		closeModal() {
			useModal().closeModal(this.store);
		},
	}
};
</script>
