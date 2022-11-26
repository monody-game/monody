<template>
  <div
    ref="player"
    :data-id="props.player.id"
    class="player__container"
    @click="send(userID, props.player.id)"
  >
    <PlayerInteractionBubble
      v-if="isVoted"
      :type="interactionType"
      :data="votedBy"
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
    </div>
    <p class="player__username">
      {{ props.player.username }}
    </p>
  </div>
</template>

<script setup>
import { useStore as useGameStore } from "../../stores/game.js";
import { useStore as useUserStore } from "../../stores/user.js";
import { computed, ref } from "vue";
import PlayerInteractionBubble from "./PlayerInteractionBubble.vue";

const props = defineProps({
	player: {
		type: Object,
		required: true
	}
});

const votedBy = ref(props.player.voted_by);
const isVoted = ref(false);
const isDead = ref(false);
const interactionType = ref("");
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
	.listen(".interaction.open", ({ interaction }) => {
		interactionType.value = interaction.type;
		if (interaction.type === "vote") {
			if (isDead.value === false) {
				player.value.classList.add("player__votable");
			}
			gameStore.currentInteractionId = interaction.id;
		}
	})
	.listen(".interaction.close", ({ interaction }) => {
		if (interaction.type === "vote") {
			if (player.value && player.value.classList.contains("player__votable")) {
				player.value.classList.remove("player__votable");
			}

			votedBy.value = [];
			isVoted.value = false;
			gameStore.currentInteractionId = "";
			gameStore.currentVote = 0;
		}
	})
	.listen(".interaction.vote", ({ data }) => {
		const votes = data.payload.votedPlayers;
		votedBy.value = [];
		isVoted.value = false;

		for (const voted in votes) {
			if (voted === props.player.id) {
				votedBy.value = votes[voted];
				isVoted.value = true;
				break;
			}
		}
	})
	.listen(".game.kill", (e) => {
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

const send = async function(votingUser, votedUser) {
	const res = await window.JSONFetch("/interactions/use", "POST", {
		id: gameStore.currentInteractionId,
		gameId:	gameId.value,
		targetId: votedUser,
		interaction: "vote"
	});

	if (res.status === 204) {
		isVoted.value = true;
	}
};
</script>
