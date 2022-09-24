<template>
  <div class="player-list__wrapper">
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
import { onMounted, ref } from "vue";
import { useRoute } from "vue-router";

const playerList = ref([]);
const loading = ref(false);
const gameStore = useStore();
const route = useRoute();

onMounted(() => {
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
});

const addUser = function (player) {
	player = injectPlayersProperties([player])[0];
	playerList.value.push(player);
	gameStore.playerList.push(player);
};

const removeUser = function (player) {
	const players = document.querySelector(".player-list__wrapper");
	Array.from(players.children).forEach((playerContainer) => {
		if (parseInt(playerContainer.children[0].dataset.id) === parseInt(player.id)) {
			gameStore.playerList = gameStore.playerList.filter((p) => p.id !== player.id);
			playerContainer.remove();
		}
	});
};

const injectPlayersProperties = function (players) {
	players.forEach((player) => {
		player.voted_by = [];
		player.role = {
			group: 0,
			name: "",
			see_has: "",
		};
	});
	return players;
};
</script>
