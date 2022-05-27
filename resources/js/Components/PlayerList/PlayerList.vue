<template>
  <div class="player-list__wrapper">
    <LoadingSpinner v-if="loading" />
    <GamePlayer
      v-for="player in playerList"
      :key="player.id"
      :player="player"
    />
  </div>
</template>

<script>
import { createApp } from "vue";
import GamePlayer from "./GamePlayer.vue";
import LoadingSpinner from "../LoadingSpinner.vue";
import { useStore } from "../../stores/game.js";

export default {
	name: "PlayerList",
	components: {
		GamePlayer: GamePlayer,
		LoadingSpinner: LoadingSpinner
	},
	data() {
		return {
			playerList: [],
			loading: false,
			gameStore: useStore(),
		};
	},
	mounted() {
		(async () => {
			window.Echo.join(`game.${this.$route.params.id}`)
				.here((users) => {
					users.forEach((user) => {
						this.addUser(user);
					});
				})
				.joining((user) => {
					this.addUser(user);
				})
				.leaving((user) => {
					this.removeUser(user);
				});
		})();
	},
	methods: {
		addUser(player) {
			const playerList = document.querySelector(".player-list__wrapper");
			const wrapper = document.createElement("div");
			player = this.injectPlayersProperties([player])[0];

			createApp(GamePlayer, {
				player: player
			}).use(window.pinia).mount(wrapper);

			this.gameStore.playerList.push(player);

			playerList.appendChild(wrapper);
		},
		removeUser(player) {
			const players = document.querySelector(".player-list__wrapper");
			Array.from(players.children).forEach((playerContainer) => {
				if (parseInt(playerContainer.children[0].dataset.id) === parseInt(player.id)) {
					this.gameStore.playerList = this.gameStore.playerList.filter((p) => p.id !== player.id);
					playerContainer.remove();
				}
			});
		},
		injectPlayersProperties(players) {
			players.forEach((player) => {
				player.voted_by = [];
				player.role = {
					group: 0,
					name: "",
					see_has: "",
				};
			});
			return players;
		},
	},
};
</script>

<style scoped></style>
