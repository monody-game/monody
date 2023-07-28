<template>
	<BaseModal wrapper="role-assignation__modal-background">
		<div
			:class="
				animationEnded
					? 'role-assignation__wrapper ' + roleOverlay
					: 'role-assignation__wrapper-large ' + roleOverlay
			"
		>
			<div
				:class="
					animationEnded
						? 'role-assignation__roles'
						: 'role-assignation__roles-large'
				"
			>
				<template v-for="n in 15" :key="n">
					<div
						v-for="role in [...roles].sort(() => Math.random() - 0.5)"
						:key="role.id"
					>
						<img :src="role.image + '?h=200&dpr=2'" :alt="role.display_name" />
					</div>
				</template>
				<div>
					<img
						class="pointer"
						:src="assignedRole.image + '?h=200&dpr=2'"
						:alt="assignedRole.display_name"
						@click="present"
					/>
				</div>
			</div>
		</div>
		<div
			v-show="animationEnded"
			ref="roleText"
			class="role-assignation__role-text"
		>
			<span>
				<span>
					{{ $t("game.you_are") }}
					<span class="bold">{{ assignedRole.display_name.toLowerCase() }}</span
					>,
				</span>
			</span>
			<span v-if="assignedRole.team.name === 'loners'">
				<span>{{ $t("game.loner_goal") }}</span>
			</span>
			<span v-else>
				<span>
					{{ $t("game.of_team") }}
					<span class="bold">{{
						assignedRole.team.display_name.toLowerCase()
					}}</span
					>.
				</span>
			</span>
			<span>
				<span class="muted">{{ $t("game.role_see_more") }}</span>
			</span>
		</div>
	</BaseModal>
</template>

<script setup>
import { nextTick, onUnmounted, ref } from "vue";
import BaseModal from "./Modal/BaseModal.vue";
import { useStore } from "../stores/chat.js";
import { useStore as useGameStore } from "../stores/game.js";
import { useStore as useRolePresentationStore } from "../stores/modals/role-presentation.js";
import { useStore as useModalStore } from "../stores/modals/modal.js";
import { useI18n } from "vue-i18n";

const props = defineProps({
	roles: {
		type: Array,
		required: true,
	},
	assignedRole: {
		type: Number,
		required: true,
	},
});

const animationEnded = ref(false);
const roleText = ref(null);
const chatStore = useStore();
const gameStore = useGameStore();
const rolePresentationStore = useRolePresentationStore();
const modalStore = useModalStore();
const { t } = useI18n();
const timeout = null;

const roles = ref(props.roles);
const assignedRole = roles.value.filter(
	(role) => role.id === parseInt(props.assignedRole),
)[0];
const roleOverlay = ref("");

const present = () => {
	rolePresentationStore.role = assignedRole;
	rolePresentationStore.opennedModal = "role-assignation";
	modalStore.open("role-presentation");
};

const onAnimationEnd = async (e) => {
	if (e.animationName === "slideRoles") {
		animationEnded.value = true;
		roleOverlay.value = "role-assignation-overlay__" + assignedRole.team.name;
	}
};

nextTick(() => {
	const children = roleText.value.children;
	let delay = 1;

	for (const span of children) {
		if (span.localName !== "span") {
			continue;
		}

		span.firstChild.style.animationDelay = `${delay}s`;
		delay += 0.7;
	}

	document.addEventListener("animationend", onAnimationEnd);

	setTimeout(() => {
		chatStore.send(t("chat.role", [assignedRole.display_name]), "info");
		gameStore.assignedRole = assignedRole;
	}, 5000);
});

onUnmounted(() => {
	document.removeEventListener("animationend", onAnimationEnd);

	if (timeout) {
		clearTimeout(timeout);
	}
});

document.documentElement.style.setProperty(
	"--role-assignation-transform-length",
	`-${roles.value.length * 15 * 100}%`,
);
</script>
