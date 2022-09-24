<template>
  <div
    ref="player"
    :data-id="props.player.id"
    class="player__container"
    @click="send(props.player.id, userID)"
  >
    <VotedBy
      v-if="isVoted"
      :player="props.player"
      :voted-by="props.player.voted_by"
    />
    <div class="player__avatar-container">
      <img
        :alt="props.player.username + `'s avatar`"
        :class="isVoted === true ? 'player__is-voted' : ''"
        :src="avatar"
        class="player__avatar"
      >
      <div class="player__is-dead">
        <span class="player__is-dead-shadow" />
        <svg v-if="isDead === true">
          <use href="/sprite.svg#death" />
        </svg>
      </div>
      <span
        v-if="props.player.role.group === 'werewolf'"
        class="player__is-wolf"
      />
    </div>
    <p class="player__username">
      {{ props.player.username }}
    </p>
  </div>
</template>

<script setup>
import VotedBy from "./VotedBy.vue";
import { useStore as useGameStore } from "../../stores/game.js";
import { useStore as useUserStore } from "../../stores/user.js";
import { computed, reactive, ref } from "vue";

const props = defineProps({
	player: {
		type: Object,
		required: true
	}
});

let isVoted = props.player.voted_by.length > 1;
const isDead = ref(false);
const votedBy = reactive(props.player.voted_by);
const gameStore = useGameStore();
const userStore = useUserStore();
const player = ref(null);

const gameId = computed(() => {
	return document.URL.split("/")[document.URL.split("/").length - 1];
});
const userID = computed(() => {
	return userStore.id;
});
const avatar = computed(() => {
	return "https://localhost" + props.player.avatar;
});

window.Echo
	.join(`game.${gameId.value}`)
	.listen(".vote.open", () => {
		player.value.classList.add("player__votable");
	})
	.listen(".vote.close", () => {
		if (player.value && player.value.classList.contains("player__votable")) {
			player.value.classList.remove("player__votable");
		}
		isVoted = false;
		gameStore.currentVote = 0;
	}).listen(".game.vote", ({ data }) => {
		const payload = data.payload;
		if (payload.votedUser !== props.player.id) {
			return;
		}
		vote(payload.votedBy, payload.votedUser);
	}).listen(".game.unvote", ({ data }) => {
		const payload = data.payload;
		if (payload.votedUser !== props.player.id) {
			return;
		}
		unVote(payload.votedBy, payload.votedUser);
	}).listen(".game.kill", (e) => {
		const killed = e.data.payload.killedUser;

		if (killed === null) {
			return;
		}

		const user = gameStore.getPlayerByID(killed);

		if (user.id === props.player.id) {
			isDead.value = true;
			player.value.setAttribute("data-is-dead", true);
		}
	});

const send = async function(votedUser, votedBy) {
	const res = await window.JSONFetch("/game/vote", "POST", {
		gameId:	gameId.value,
		userId: votedUser
	});

	if (res.status !== 204) {
		await unVote(votedBy, votedUser);
	}
};

const vote = async function (votingUser, votedUser) {
	if (
		gameStore.currentVote > 0
	) {
		await unVote(votingUser, gameStore.currentVote);
		return;
	}

	isVoted = true;
	gameStore.setVote({
		userID: votedUser,
		votedBy: votingUser,
	});
};

const unVote = async function (votingUser, votedUser) {
	if (gameStore.getVotes(votedUser).length - 1 < 1) {
		isVoted = false;
	}

	gameStore.currentVote = 0;
	votedBy.splice(votedBy.indexOf(votedBy), 1);
	gameStore.unVote({
		userID: votedUser,
		votedBy: votingUser,
	});
};
</script>
