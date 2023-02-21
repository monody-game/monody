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
  </div>
</template>

<script setup>
import GamePlayer from "./GamePlayer.vue";
import LogoSpinner from "../Spinners/LogoSpinner.vue";
import { useStore } from "../../stores/game.js";
import { ref } from "vue";
import { useRoute } from "vue-router";

const playerList = ref([]);
const playerListNode = ref(null);
const loading = ref(false);
const gameStore = useStore();
const route = useRoute();

window.Echo.join(`game.${route.params.id}`)
	.here((users) => {
		users.forEach((user) => {
			addUser(user);
		});
	})
	.joining((user) => {
		addUser(user);
	})
	.leaving((user) => {
		removeUser(user);
	});

const addUser = function (player) {
	player = injectPlayersProperties([player])[0];
	if (!playerList.value.includes(player)) {
		console.warn(`User ${player.id} was already shown in game`);
		playerList.value.push(player);
	}
	if (!gameStore.playerList.includes(player)) {
		console.warn(`User ${player.id} was already in the list of players`);
		gameStore.playerList.push(player);
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
