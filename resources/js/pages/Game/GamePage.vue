<template>
  <div class="game-page__container day">
    <div class="game-page__header">
      <a
        class="game-page__home-link"
        @click.prevent="disconnect()"
      >
        <svg
          fill="none"
          height="40"
          viewBox="0 0 40 40"
          width="40"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M37.4556 19.2111L20.789 2.54445C20.5808 2.3375 20.2992 2.22134 20.0056 2.22134C19.7121 2.22134 19.4305 2.3375 19.2223 2.54445L2.55563 19.2111C2.3736 19.4237 2.27848 19.6971 2.28928 19.9767C2.30008 20.2564 2.41601 20.5216 2.61389 20.7195C2.81177 20.9174 3.07704 21.0333 3.35668 21.0441C3.63632 21.0549 3.90974 20.9598 4.12229 20.7778L20.0001 4.9L35.8778 20.7889C36.0904 20.9709 36.3638 21.066 36.6435 21.0552C36.9231 21.0444 37.1884 20.9285 37.3862 20.7306C37.5841 20.5327 37.7001 20.2675 37.7109 19.9878C37.7217 19.7082 37.6265 19.4348 37.4445 19.2222L37.4556 19.2111Z"
            fill="currentColor"
          />
          <path
            d="M31.1111 35.5556H25.5555V24.4444H14.4444V35.5556H8.88885V20L6.66663 22.2222V35.5556C6.66663 36.1449 6.90075 36.7102 7.3175 37.1269C7.73425 37.5437 8.29948 37.7778 8.88885 37.7778H16.6666V26.6667H23.3333V37.7778H31.1111C31.7004 37.7778 32.2657 37.5437 32.6824 37.1269C33.0992 36.7102 33.3333 36.1449 33.3333 35.5556V21.9556L31.1111 19.7333V35.5556Z"
            fill="currentColor"
          />
        </svg>
        <p>Accueil</p>
      </a>
      <Suspense><GameCounter /></Suspense>
      <svg
        class="game-page__details"
        @click="modalStore.open('game-details')"
      >
        <use href="/sprite.svg#question" />
      </svg>
    </div>
    <div class="game-page__main">
      <Transition name="modal">
        <RoleAssignationPopup
          v-if="assignationPopupStore.isOpenned"
          :roles="store.roles"
          :assigned-role="assignedRole"
        />
      </Transition>
      <Chat />
      <LogoSpinner v-if="loading" />
      <PlayerList />
    </div>
    <Transition name="modal">
      <ShareGameModal v-if="shareModalStore.isOpenned" />
    </Transition>
    <Transition name="modal">
      <ActivityConfirmationModal v-if="activityConfirmationModalStore.isOpenned" />
    </Transition>
    <Transition name="modal">
      <GameDetailsModal v-if="modalStore.opennedModal === 'game-details'" />
    </Transition>
    <Transition name="modal">
      <GameVocalInvitation v-if="store.type === 1 && vocalInvitationStore.isOpenned === true" />
    </Transition>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { onBeforeRouteLeave, useRoute } from "vue-router";
import { useStore } from "../../stores/game.js";
import { useStore as usePopupStore } from "../../stores/modals/popup.js";
import { useStore as useAssignationPopupStore } from "../../stores/modals/role-assignation.js";
import { useStore as useModalStore } from "../../stores/modals/modal.js";
import { useStore as useUserStore } from "../../stores/user.js";
import { useStore as useShareModalStore } from "../../stores/modals/share-game-modal.js";
import { useStore as useActivityConfirmationModalStore } from "../../stores/modals/activity-confirmation-modal.js";
import { useStore as useVocalInvitationStore } from "../../stores/modals/vocal-invitation-store.js";
import { useStore as useChatStore } from "../../stores/chat.js";
import RoleAssignationPopup from "../../Components/RoleAssignationPopup.vue";
import GameCounter from "../../Components/GameCounter.vue";
import Chat from "../../Components/Chat/TheChat.vue";
import PlayerList from "../../Components/PlayerList/PlayerList.vue";
import LogoSpinner from "../../Components/Spinners/LogoSpinner.vue";
import ShareGameModal from "../../Components/Modal/ShareGameModal.vue";
import ActivityConfirmationModal from "../../Components/Modal/ActivityConfirmationModal.vue";
import GameDetailsModal from "../../Components/Modal/GameDetailsModal.vue";
import GameVocalInvitation from "../../Components/Modal/GameVocalInvitation.vue";

const route = useRoute();
const store = useStore();

const chatStore = useChatStore();
const shareModalStore = useShareModalStore();
const popupStore = usePopupStore();
const userStore = useUserStore();
const assignationPopupStore = useAssignationPopupStore();
const modalStore = useModalStore();
const activityConfirmationModalStore = useActivityConfirmationModalStore();
const vocalInvitationStore = useVocalInvitationStore();

const gameId = route.params.id;
const loading = ref(false);
let roles = store.roles;
const assignedRole = ref(0);

const actions = await window.JSONFetch("/interactions/actions", "GET");
store.availableActions = actions.data.actions;

if (localStorage.getItem("show_share") === "true") {
	modalStore.open("share-game-modal");
}

window.Echo.join(`game.${gameId}`)
	.listen(".game.data", async ({ data }) => {
		const e = data.payload;
		store.owner = e.owner;

		if (Object.keys(e.roles) !== roles.map(role => role.id) || roles.length === 0) {
			roles = [];
			for (const role in e.roles) {
				const res = await window.JSONFetch(`/roles/get/${role}`);
				const rolePayload = res.data.role;
				rolePayload.count = e.roles[role];
				roles.push(rolePayload);
			}
		}

		store.voted_users = e.voted_users;
		store.dead_users = e.dead_users;
		store.chat_locked = e.chat_locked;
		store.assignedRole = e.role ?? {};
		store.contaminated = e.contaminated;
		store.roles = roles;
		store.type = e.type;

		if (e.type === 1 && e.discord === null) {
			const res = await window.JSONFetch(`/game/${gameId}/discord`);
			store.discord = res.data.data;
		} else {
			store.discord = e.discord;
		}

		if (e.current_interactions.length > 0) {
			store.currentInteractionId = e.current_interactions[0].id;
		}
	})
	.listen(".game.role-assign", async (role_id) => {
		const res = await window.JSONFetch(`/roles/get/${role_id}`, "GET");
		const role = res.data.role;
		store.setRole(userStore.id, role);
		assignedRole.value = role.id;
		modalStore.opennedModal = "role-assignation";
		assignationPopupStore.isOpenned = true;

		setTimeout(() => {
			if (assignationPopupStore.isOpenned) {
				modalStore.close();
			}
		}, 20000);
	})
	.listen(".game.kill", (e) => {
		const killed = e.data.payload.killedUser;

		if (killed === null) {
			return;
		}

		store.dead_users.push(killed);
	})
	.listen(".game.mayor", (e) => {
		store.mayor = e.data.payload.mayor;
	})
	.listen(".game.werewolves", (e) => {
		store.werewolves = e.data.payload.list;
	})
	.listen(".interaction.open", ({ interaction }) => {
		if (interaction.type === "angel") {
			store.angel_target = interaction.data;
			chatStore.send(
				`Votre cible est : ${store.getPlayerByID(interaction.data).username}. Si votre cible vient à mourir avant la prochaine nuit, vous gagnerez la partie instantanément.`,
				"info"
			);
		}
	})
	.listen(".interaction.surly_werewolf:bite", () => {
		chatStore.send("Vous avez été mordu par le loup hargneux. Vos blessures semblent graves et vous survivrez pas à la prochaine nuit.", "warn");
	})
	.listen(".interaction.parasite:contaminate", ({ data }) => {
		if (store.contaminated.length === 0) {
			chatStore.send("Vous avez été contaminé par le parasite.", "warn");
		}

		store.contaminated = data.payload.contaminated;
	})
	.listen(".interaction.cupid:pair", ({ data }) => {
		store.couple = data.payload.couple;
	});

const leave = () => {
	window.Echo.leave(`game.${gameId}`);
	vocalInvitationStore.$reset();
	store.$reset();
};

onBeforeRouteLeave(leave);
window.addEventListener("unload", leave);

const disconnect = async function () {
	popupStore.setPopup({
		warn: {
			content: "Voulez-vous vraiment quitter la partie ?",
			note: "Si oui, ",
			link: "/play",
			link_text: "cliquez ici."
		}
	});
};
</script>
