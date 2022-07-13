<template>
  <div
    ref="player"
    :data-id="player.id"
    class="player__container"
    @click="send(player.id, userID)"
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
      <div class="player__is-dead">
        <span class="player__is-dead-shadow" />
        <svg v-if="isDead === true">
          <use href="/sprite.svg#death" />
        </svg>
      </div>
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
			isDead: false,
			gameStore: useGameStore(),
			userStore: useUserStore(),
			votedBy: this.player.voted_by
		};
	},
	computed: {
		gameId() {
			return document.URL.split("/")[document.URL.split("/").length - 1];
		},
		userID() {
			return this.userStore.id;
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
				this.gameStore.currentVote = 0;
			}).listen(".game.vote", ({ data }) => {
				const payload = data.payload;
				if (payload.votedUser !== this.player.id) {
					return;
				}
				this.vote(payload.votedBy, payload.votedUser);
			}).listen(".game.unvote", ({ data }) => {
				const payload = data.payload;
				if (payload.votedUser !== this.player.id) {
					return;
				}
				this.unVote(payload.votedBy, payload.votedUser);
			}).listen(".game.kill", (e) => {
				const killed = e.data.payload.killedUser;

				if (killed === null) {
					return;
				}

				const user = this.gameStore.getPlayerByID(killed);

				if (user.id === this.player.id) {
					this.isDead = true;
					this.$refs.player.setAttribute("data-is-dead", true);
				}
			});
	},
	methods: {
		async send(votedUser, votedBy) {
			const res = await window.JSONFetch("/game/vote", "POST", {
				gameId:	this.gameId,
				userId: votedUser
			});

			if (res.status !== 204) {
				await this.unVote(votedBy, votedUser);
			}
		},
		vote: async function (votedBy, votedUser) {
			if (
				this.gameStore.currentVote > 0
			) {
				await this.unVote(votedBy, this.gameStore.currentVote);
				return;
			}

			this.isVoted = true;
			this.gameStore.setVote({
				userID: votedUser,
				votedBy: votedBy,
			});
		},
		unVote: async function (votedBy, votedUser) {
			console.log(this.gameStore.getVotes(votedUser));
			if (this.gameStore.getVotes(votedUser).length - 1 < 1) {
				this.isVoted = false;
			}

			this.gameStore.currentVote = 0;
			this.votedBy.splice(this.votedBy.indexOf(votedBy), 1);
			this.gameStore.unVote({
				userID: votedUser,
				votedBy: votedBy,
			});
		},
	},
};
</script>
