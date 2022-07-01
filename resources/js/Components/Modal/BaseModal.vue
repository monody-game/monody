<template>
  <div
    ref="modal-background"
    class="modal__background"
    @click="closeModal()"
  >
    <div
      v-if="gameCreationModal.isOpenned || profileModal.isOpenned"
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
import { useStore as useGameCreationModal } from "../../stores/GameCreationModal";
import { useStore as useProfileModal } from "../../stores/ProfileModal";

export default {
	name: "BaseModal",
	data() {
		return {
			gameCreationModal: useGameCreationModal(),
			profileModal: useProfileModal(),
		};
	},
	mounted() {
		this.$refs.modal.focus();
	},
	methods: {
		closeModal() {
			if (this.gameCreationModal.isOpenned) {
				this.gameCreationModal.isOpenned = false;
				document.documentElement.style.removeProperty(
					"--villager-balance-width"
				);
				document.documentElement.style.removeProperty(
					"--werewolf-balance-width"
				);
			} else if (this.profileModal.isOpenned) {
				this.profileModal.isOpenned = false;
			}
		},
	}
};
</script>
