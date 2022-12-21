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
        class="btn large secondary"
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
import { useRouter } from "vue-router";
import { ref } from "vue";

const currentPage = ref(1);
const totalPage = ref(2);
const store = useStore();
const router = useRouter();
const gameId = ref("");

const closeModal = function () {
	reset();
};

const notEnoughSelectedRoles = function () {
	const selectedRoles = store.selectedRoles;
	// return selectedRoles.length < 5;
	// TODO: replace line below with line above
	return selectedRoles.length < 2;
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

	reset();

	if (store.gameId !== 0) {
		await router.push("/game/" + gameId.value);
	}
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
