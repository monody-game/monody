<template>
  <BaseModal @keyup.esc="closeModal()">
    <p id="modal__title">
      Création d'une partie ({{ currentPage }}/{{ totalPage }})
    </p>
    <div class="modal__page">
      <RolesModalPage v-if="currentPage === 1" />
      <GameStateModalPage v-else-if="currentPage === 2" />
      <ShareModalPage v-else-if="currentPage === 3" />
    </div>
    <div class="modal__buttons">
      <button
        class="btn large"
        @click="closeModal()"
      >
        Annuler
      </button>
      <div class="modal__buttons-right">
        <button
          :class="currentPage === 1 ? 'disable-hover' : ''"
          :disabled="currentPage === 1"
          class="btn large"
          @click="previousPage()"
        >
          Précédent
        </button>
        <button
          v-if="currentPage !== totalPage"
          :class="notEnoughSelectedRoles() === true ? 'disable-hover' : ''"
          :disabled="notEnoughSelectedRoles()"
          class="btn large"
          @click="nextPage()"
        >
          Suivant
        </button>
        <button
          v-if="currentPage === totalPage"
          class="btn large"
          @click="finish()"
        >
          Terminer
        </button>
      </div>
    </div>
  </BaseModal>
</template>

<script>
import RolesModalPage from "./Pages/Roles/RolesModalPage.vue";
import GameStateModalPage from "./Pages/GameState/GameStateModalPage.vue";
import ShareModalPage from "./Pages/ShareModalPage.vue";
import BaseModal from "./BaseModal.vue";
import { useStore } from "../../stores/GameCreationModal.js";

export default {
	name: "GameCreationModal",
	components: {
		BaseModal,
		RolesModalPage,
		GameStateModalPage,
		ShareModalPage,
	},
	data() {
		return {
			currentPage: 1,
			totalPage: 3,
			error: "",
			store: useStore(),
			gameId: 0,
		};
	},
	methods: {
		closeModal() {
			this.reset();
		},
		notEnoughSelectedRoles() {
			const selectedRoles = this.store.selectedRoles;
			// return selectedRoles.length < 5;
			// TODO: replace line below with line above
			return selectedRoles.length < 2;
		},
		async nextPage() {
			if (this.currentPage + 1 > this.totalPage) {
				return;
			}

			if (this.currentPage === this.totalPage - 1) {
				const res = await window.JSONFetch("/game/new", "POST", {
					roles: this.store.selectedRoles,
					users: []
				});
				const id = res.data.game.id;
				this.gameId = id;
				this.store.gameId = id;
			}

			this.currentPage++;
		},
		async finish() {
			this.reset();

			if (this.gameId !== 0) {
				await this.$router.push("/game/" + this.gameId);
			}
		},
		reset() {
			this.store.$reset();

			document.documentElement.style.removeProperty(
				"--villager-balance-width"
			);
			document.documentElement.style.removeProperty(
				"--werewolf-balance-width"
			);
		},
		previousPage() {
			if (this.currentPage + 1 < this.totalPage) {
				return;
			}
			this.currentPage = this.currentPage - 1;
		},
	}
};
</script>
