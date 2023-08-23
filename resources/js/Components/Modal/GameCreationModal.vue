<template>
	<BaseModal>
		<header>
			<h3>{{ $t("new_game.title") }}</h3>
			<p class="modal__page-status">({{ currentPage }}/{{ totalPage }})</p>
		</header>
		<div class="modal__page">
			<RolesModalPage v-if="currentPage === 1" />
			<GameTypeSelectionModalPage
				v-if="currentPage === 2"
				:has-linked="userStore.discord_linked_at !== null"
			/>
		</div>
		<div class="modal__buttons">
			<button class="btn medium" @click="modalStore.close()">
				{{ $t("modal.cancel") }}
			</button>
			<div class="modal__buttons-right">
				<button
					class="btn medium"
					:class="currentPage === 1 ? 'disabled' : ''"
					:disabled="currentPage === 1"
					@click.prevent="previous"
				>
					{{ $t("modal.previous") }}
				</button>
				<button
					v-if="currentPage !== totalPage"
					class="btn medium"
					:class="notEnoughSelectedRoles() === true ? 'disabled' : ''"
					:disabled="notEnoughSelectedRoles()"
					@click.prevent="next"
				>
					{{ $t("modal.next") }}
				</button>
				<button
					v-if="currentPage === totalPage"
					:class="notEnoughSelectedRoles() === true ? 'disabled' : ''"
					:disabled="notEnoughSelectedRoles()"
					class="btn medium"
					@click="finish()"
				>
					{{ $t("modal.finish") }}
				</button>
			</div>
		</div>

		<Transition name="modal">
			<RolePresentationModal v-if="rolePresentationStore.isOpenned" />
		</Transition>
	</BaseModal>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useStore } from "../../stores/modals/game-creation-modal.js";
import { useStore as useUserStore } from "../../stores/user.js";
import { useStore as useRolePresentationStore } from "../../stores/modals/role-presentation.js";
import { useStore as useModalStore } from "../../stores/modals/modal.js";
import BaseModal from "./BaseModal.vue";
import RolesModalPage from "./Pages/Roles/RolesModalPage.vue";
import GameTypeSelectionModalPage from "./Pages/GameTypeSelectionModalPage.vue";
import RolePresentationModal from "./RolePresentationModal.vue";

const router = useRouter();
const store = useStore();
const userStore = useUserStore();
const modalStore = useModalStore();
const rolePresentationStore = useRolePresentationStore();

const gameId = ref("");
const currentPage = ref(1);
const totalPage = ref(2);

const notEnoughSelectedRoles = function () {
	const selectedIds = store.selectedRoles;
	const selectedRoles = [];

	for (const role of store.roles) {
		if (selectedIds.indexOf(role.id) !== -1) {
			role.count = store.getRoleCountById(role.id);
			selectedRoles.push(role);
		}
	}

	return (
		selectedIds.length < 2 ||
		!(
			selectedRoles.filter((role) => role.team.id === 1).length >= 1 &&
			selectedRoles.filter((role) => role.team.id === 2).length >= 1
		)
	);
};

const previous = function () {
	if (currentPage.value > 1) {
		currentPage.value--;
	}
};

const next = function () {
	if (currentPage.value < totalPage.value) {
		currentPage.value++;
	}
};

const finish = async function () {
	const res = await window.JSONFetch("/game", "PUT", {
		roles: store.selectedRoles,
		type: store.gameType,
	});

	gameId.value = res.data.game.id;

	if (store.gameId !== 0) {
		modalStore.close();
		store.$reset();
		localStorage.setItem("show_share", true);
		await router.push("/game/" + gameId.value);
	}
};
</script>
