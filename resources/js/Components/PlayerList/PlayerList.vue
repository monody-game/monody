<template>
  <div
    ref="playerListNode"
    class="player-list__wrapper"
  >
    <LogoSpinner v-if="loading.value === true" />
    <GamePlayer
      v-for="player in playerList"
      :key="player.id"
      :player="player"
    />
    <img
      v-if="typeof gameStore.assignedRole.id !== 'undefined'"
      :src="gameStore.assignedRole.image"
      :alt="gameStore.assignedRole.display_name"
      :title="gameStore.assignedRole.display_name"
      class="game-page__role"
      @click="present()"
    >
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRoute } from "vue-router";
import { useStore } from "../../stores/game.js";
import { useStore as useChatStore } from "../../stores/chat.js";
import { useStore as useRolePresentationStore } from "../../stores/modals/role-presentation.js";
import { useStore as useModalStore } from "../../stores/modals/modal.js";
import LogoSpinner from "../Spinners/LogoSpinner.vue";
import GamePlayer from "./GamePlayer.vue";

const playerList = ref([]);
const playerListNode = ref(null);
const loading = ref(false);
const gameStore = useStore();
const route = useRoute();
const chatStore = useChatStore();
const rolePresentationStore = useRolePresentationStore();
const modalStore = useModalStore();

window.Echo.join(`game.${route.params.id}`)
	.here((users) => {
		users.forEach((user) => {
			addUser(user);
		});
	})
	.joining((user) => {
		chatStore.send(true, "inandout_alert", user.username);
		addUser(user);
	})
	.leaving((user) => {
		chatStore.send(false, "inandout_alert", user.username);
		removeUser(user);
	});

const present = () => {
	rolePresentationStore.role = gameStore.assignedRole;
	modalStore.open("role-presentation");
};

const addUser = function (player) {
	player = injectPlayersProperties([player])[0];
	if (!playerList.value.includes(player)) {
		playerList.value.push(player);
	} else {
		console.warn(`User ${player.id} was already shown in game`);
	}

	if (!gameStore.playerList.includes(player)) {
		gameStore.playerList.push(player);
	} else {
		console.warn(`User ${player.id} was already in the list of players`);
	}
};

const removeUser = function (player) {
	const children = playerListNode.value.children;

	for (const playerNode of children) {
		if (playerNode.dataset.id === player.id) {
			gameStore.playerList = gameStore.playerList.filter((p) => p.id !== player.id);
			playerNode.remove();
		}
	}
};

const injectPlayersProperties = function (players) {
	players.forEach((player) => {
		player.voted_by = [];
	});
	return players;
};
</script>
