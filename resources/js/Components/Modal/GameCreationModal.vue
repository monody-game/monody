<template>
  <BaseModal>
    <p id="modal__title">
      Création d'une partie ({{ currentPage }}/{{ totalPage }})
    </p>
    <div class="modal__page">
      <RolesModalPage v-if="currentPage === 1" />
      <GameStateModalPage v-else-if="currentPage === 2" />
      <!--      <ShareModalPage v-else-if="currentPage === 3" />-->
    </div>
    <div class="modal__buttons">
      <button
        class="btn medium secondary"
        @click="closeModal()"
      >
        Annuler
      </button>
      <div class="modal__buttons-right">
        <button
          :class="currentPage === 1 ? 'disable-hover' : ''"
          :disabled="currentPage === 1"
          class="btn medium"
          @click="previousPage()"
        >
          Précédent
        </button>
        <button
          v-if="currentPage !== totalPage"
          :class="notEnoughSelectedRoles() === true ? 'disable-hover' : ''"
          :disabled="notEnoughSelectedRoles()"
          class="btn medium"
          @click="nextPage()"
        >
          Suivant
        </button>
        <button
          v-if="currentPage === totalPage"
          class="btn medium"
          @click="finish()"
        >
          Terminer
        </button>
      </div>
    </div>
  </BaseModal>
</template>

<script setup>
import RolesModalPage from "./Pages/Roles/RolesModalPage.vue";
import GameStateModalPage from "./Pages/GameState/GameStateModalPage.vue";
import ShareModalPage from "./Pages/ShareModalPage.vue";
import BaseModal from "./BaseModal.vue";
import { useStore } from "../../stores/GameCreationModal.js";
import { useStore as useGameStore } from "../../stores/game.js";
import { useRouter } from "vue-router";
import { ref } from "vue";

const currentPage = ref(1);
const totalPage = ref(2);
const store = useStore();
const router = useRouter();
const gameId = ref("");
const gameStore = useGameStore();

const closeModal = function () {
	reset();
};

const notEnoughSelectedRoles = function () {
	const selectedIds = store.selectedRoles;
	const selectedRoles = [];

	for (const role of store.roles) {
		if (selectedIds.indexOf(role.id) !== -1) {
			role.count = store.getRoleCountById(role.id);
			selectedRoles.push(role);
		}
	}

	// return selectedIds.length < 5;
	// TODO: replace line below with line above
	return selectedIds.length < 2 ||
		!(
			selectedRoles.filter(role => role.team_id === 1).length >= 1 &&
			selectedRoles.filter(role => role.team_id === 2).length >= 1
		);
};

const nextPage = async function() {
	if (currentPage.value + 1 > totalPage.value) {
		return;
	}

	currentPage.value++;
};

const finish = async function() {
	const res = await window.JSONFetch("/game", "PUT", {
		roles: store.selectedRoles
	});

	store.gameId = res.data.game.id;
	gameId.value = res.data.game.id;

	if (store.gameId !== 0) {
		gameStore.roles = store.roles.filter(role => store.selectedRoles.includes(role.id));
		reset();

		await router.push("/game/" + gameId.value);
	}

	reset();
};

const reset = function () {
	store.close();
};

const previousPage = function () {
	if (currentPage.value + 1 < totalPage.value) {
		return;
	}
	currentPage.value = currentPage.value - 1;
};
</script>
