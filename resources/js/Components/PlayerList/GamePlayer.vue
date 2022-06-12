<template>
  <div
    ref="player"
    :data-id="player.id"
    class="player__container"
    @click="vote(userID, player.id)"
  >
    <VotedBy
      v-if="isVoted"
      :player="player"
      :voted-by="votedBy"
    />
    <div class="player__avatar-container">
      <img
        :alt="player.username + `'s avatar`"
        :class="isVoted === true ? 'player__is-voted' : ''"
        :src="getAvatar"
        class="player__avatar"
      >
      <span
        v-if="player.role.group === 'werewolf'"
        class="player__is-wolf"
      />
    </div>
    <p class="player__username">
      {{ player.username }}
    </p>
  </div>
</template>

<script>
import VotedBy from "./VotedBy.vue";
import { useStore as useGameStore } from "../../stores/game.js";
import { useStore as useUserStore } from "../../stores/user.js";

export default {
	name: "GamePlayer",
	components: { VotedBy },
	props: {
		player: Object
	},
	data() {
		return {
			isVoted: this.player.voted_by.length > 1,
			gameStore: useGameStore(),
			userStore: useUserStore(),
			gameId: document.URL.split("/")[document.URL.split("/").length - 1]
		};
	},
	computed: {
		userID() {
			return this.userStore.id;
		},
		votedBy() {
			return this.player.voted_by;
		},
		getAvatar() {
			return "https://localhost" + this.player.avatar;
		}
	},
	created() {
		window.Echo
			.join(`game.${this.gameId}`)
			.listen(".vote.open", () => {
				this.$refs.player.classList.add("player__votable");
			})
			.listen(".vote.close", () => {
				const player = this.$refs.player;
				if (player && player.classList.contains("player__votable")) {
					player.classList.remove("player__votable");
				}
				this.isVoted = false;
				this.gameStore.clearVotes;
			}).listen(".game.vote", ({ data }) => {
				const payload = data.payload;
				if (payload.votedUser !== this.player.id || payload.votedBy === this.userID) {
					return;
				}
				this.vote(payload.votedBy, payload.votedUser, false);
			}).listen(".game.unvote", ({ data }) => {
				const payload = data.payload;
				if (payload.votedUser !== this.player.id || payload.votedBy === this.userID) {
					return;
				}
				this.unVote(payload.votedBy, payload.votedUser, false);
			});
	},
	methods: {
		vote: async function (votedBy, votedUser, makeRequest = true) {
			if (typeof votedBy === "undefined") {
				return;
			}

			if (this.votedBy.includes(votedBy)) {
				await this.unVote(votedBy, votedUser);
				return;
			}

			if (
				this.gameStore.getCurrentVote > 0
			) {
				await this.unVote(votedBy, this.gameStore.getCurrentVote);
				return;
			}

			this.isVoted = true;

			this.gameStore.setVote({
				userID: votedUser,
				votedBy: votedBy,
			});

			if (makeRequest) {
				const res = await window.JSONFetch("/game/vote", "POST", {
					gameId:	this.gameId,
					userId: votedUser
				});

				if (res.status !== 204) {
					await this.unVote(votedBy, votedUser, false);
				}
			}
		},
		unVote: async function (votedBy, votedUser, makeRequest = true) {
			if (typeof votedBy === "undefined") {
				return;
			}
			if (this.player.id === votedUser) {
				this.isVoted = false;
			}
			this.gameStore.currentVote = 0;
			this.votedBy.splice(this.votedBy.indexOf(votedBy), 1);
			this.gameStore.unVote({
				userID: votedUser,
				votedBy: votedBy,
			});

			if (makeRequest) {
				const res = await window.JSONFetch("/game/vote", "POST", {
					gameId:	this.gameId,
					userId: votedUser
				});

				if (res.status !== 204) {
					await this.vote(votedBy, votedUser, false);
				}
			}
		},
	},
};
</script>

<style scoped></style>
