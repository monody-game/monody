<template>
	<BaseModal class="game-details">
		<header>
			<h3>{{ $t("game_details.title") }}</h3>
		</header>
		<div class="modal__page game-details__wrapper">
			<div class="game-details__group">
				{{ $t("game_details.owner") }}
				<div class="game-details__owner">
					<img
						:src="gameStore.owner.avatar + '?w=50&dpr=2'"
						:alt="gameStore.owner.username + '\'s avatar'"
					/>
					<div class="game-details__owner-right">
						{{ gameStore.owner.username }}
						<div class="game-details__owner-stats">
							<div title="Niveau">
								<svg>
									<use href="/sprite.svg#level" />
								</svg>
								<p>{{ gameStore.owner.level }}</p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="game-details__roles">
				{{ $t("game_details.roles") }}
				<div class="roles__list">
					<RoleSelector
						v-for="role in roles"
						:key="role.id"
						:role="role"
						class="roles__item"
						:operations="false"
						:presentable="true"
						@role="(role) => present(role)"
					/>
				</div>
				<RolesBalance :selected-roles="roles" />
			</div>
		</div>
		<div class="modal__buttons">
			<div class="modal__buttons-right">
				<button class="btn medium" @click="store.close()">
					{{ $t("modal.close") }}
				</button>
			</div>
		</div>
	</BaseModal>
</template>

<script setup>
import { useStore as useGameStore } from "../../stores/game.js";
import { useStore as useRolePresentationStore } from "../../stores/modals/role-presentation.js";
import { useStore } from "../../stores/modals/modal.js";
import BaseModal from "./BaseModal.vue";
import RoleSelector from "./Pages/Roles/RoleSelector.vue";
import RolesBalance from "./Pages/Roles/RolesBalance.vue";

const gameStore = useGameStore();
const store = useStore();
const roles = gameStore.roles;
const rolePresentationStore = useRolePresentationStore();

const present = (role) => {
	rolePresentationStore.role = role;
	rolePresentationStore.opennedModal = "game-details";
	store.open("role-presentation");
};
</script>
