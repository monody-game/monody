<template>
  <div
    ref="player"
    :data-id="props.player.id"
    class="player__container"
    @click="send(userID, props.player.id)"
  >
    <PlayerInteractionBubble
      v-if="shouldShowBubble"
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
import { computed, onMounted, ref } from "vue";
import PlayerInteractionBubble from "./PlayerInteractionBubble.vue";
import ChatService from "../../services/ChatService.js";

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
const shouldShowBubble = ref(false);

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

		switch (interaction.type) {
		case "vote":
		case "werewolves":
			if (isDead.value === false) {
				player.value.classList.add("player__votable");
			}
			break;
		case "psychic":
			const gamePlayer = gameStore.getPlayerByID(props.player.id);

			if (gamePlayer.role && gamePlayer.role.name === "psychic") {
				ChatService.sendMessage({
					"content": "Cliquez sur un joueur pour en connaitre le rôle !",
					"type": "info"
				});
				player.value.classList.add('player__hover-disabled')
			} else {
				player.value.classList.add("player__psychic-hover");
			}
		}

		gameStore.currentInteractionId = interaction.id;
	})
	.listen(".interaction.close", ({ interaction }) => {
		switch (interaction.type) {
		case "vote":
		case "werewolves":
			if (player.value && player.value.classList.contains("player__votable")) {
				player.value.classList.remove("player__votable");
			}

			votedBy.value = [];
			isVoted.value = false;
			gameStore.currentVote = 0;
			break;
		case "psychic":
			player.value.classList.remove("player__psychic-hover");
		}

		gameStore.currentInteractionId = "";
	})
	.listen(".interaction.vote", ({ data }) => addVote(data))
	.listen(".interaction.werewolves:kill", ({ data }) => addVote(data))
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
		action: gameStore.availableActions[interactionType.value]
	});

	if (res.status === 204) {
		isVoted.value = true;
	}

	if (interactionType.value === "psychic") {
		const role = await window.JSONFetch(`/roles/get/${res.data.response}`, "GET");
		ChatService.sendMessage({
			"content": `Vous avez choisi d'espionner le rôle de ${props.player.username} qui est ${role.data.role.display_name}`,
			"type": "success"
		});
	}
};

const addVote = (data) => {
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
};

onMounted(() => {
	shouldShowBubble.value = isVoted.value;
});
</script>
