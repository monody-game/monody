<template>
  <BaseModal>
    <header>
      <h3>Création d'une partie</h3>
    </header>
    <div class="modal__page">
      <RolesModalPage />
    </div>
    <div class="modal__buttons">
      <button
        class="btn medium"
        @click="store.close()"
      >
        Annuler
      </button>
      <div class="modal__buttons-right">
        <button
          :class="notEnoughSelectedRoles() === true ? 'disabled' : ''"
          :disabled="notEnoughSelectedRoles()"
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
import BaseModal from "./BaseModal.vue";
import { useStore } from "../../stores/modals/game-creation-modal.js";
import { useStore as useGameStore } from "../../stores/game.js";
import { useStore as useUserStore } from "../../stores/user.js";
import { useRouter } from "vue-router";
import { ref } from "vue";

const store = useStore();
const router = useRouter();
const gameId = ref("");
const gameStore = useGameStore();
const userStore = useUserStore();

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
			selectedRoles.filter(role => role.team.id === 1).length >= 1 &&
			selectedRoles.filter(role => role.team.id === 2).length >= 1
		);
};

const finish = async function() {
	const res = await window.JSONFetch("/game", "PUT", {
		roles: store.selectedRoles
	});

	store.gameId = res.data.game.id;
	gameId.value = res.data.game.id;

	if (store.gameId !== 0) {
		gameStore.roles = store.roles.filter(role => store.selectedRoles.includes(role.id));
		gameStore.owner = userStore.getUser;

		store.close();
		localStorage.setItem("show_share", true);
		await router.push("/game/" + gameId.value);
	}
};
</script>
