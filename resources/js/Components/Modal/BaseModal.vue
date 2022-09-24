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

<script setup>
import { useStore as useGameCreationModal } from "../../stores/GameCreationModal";
import { useStore as useProfileModal } from "../../stores/ProfileModal";
import { ref, onMounted } from "vue";

const gameCreationModal = useGameCreationModal();
const profileModal = useProfileModal();
const modal = ref(null);

onMounted(() => {
	modal.value.focus();
});

const closeModal = function () {
	if (gameCreationModal.isOpenned) {
		gameCreationModal.isOpenned = false;
		document.documentElement.style.removeProperty(
			"--villager-balance-width"
		);
		document.documentElement.style.removeProperty(
			"--werewolf-balance-width"
		);
	} else if (profileModal.isOpenned) {
		profileModal.isOpenned = false;
	}
};
</script>
