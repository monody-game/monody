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
        :src="props.player.avatar + '?h=120&dpr=2'"
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
import { computed, onMounted, ref } from "vue";
import { useStore as useGameStore } from "../../stores/game.js";
import { useStore as useUserStore } from "../../stores/user.js";
import { useStore as useChatStore } from "../../stores/chat.js";
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
const chatStore = useChatStore();
const player = ref(null);
const gamePlayer = gameStore.getPlayerByID(props.player.id);

onMounted(() => {
	if (player.value !== null) {
		gameStore.playerRefs.push(player);
	}
});

const gameId = computed(() => {
	return document.URL.split("/")[document.URL.split("/").length - 1];
});

const userID = computed(() => {
	return userStore.id;
});

window.Echo
	.join(`game.${gameId.value}`)
	.listen(".interaction.open", ({ interaction }) => {
		interactionType.value = interaction.type;
		gameStore.currentInteractionId = interaction.id;

		switch (interaction.type) {
		case "vote":
		case "werewolves":
			if (isDead.value === false) {
				player.value.classList.add("player__votable");
			}
			break;
		case "psychic":
			if (gamePlayer.role && gamePlayer.role.name === "psychic") {
				chatStore.send("Cliquez sur un joueur pour en connaitre le rôle !", "info");
				player.value.classList.add("player__hover-disabled");
			} else {
				player.value.classList.add("player__psychic-hover");
			}
			break;
		case "witch":
			setupWitchActions(interaction);
			break;
		case "infected_werewolf":
			if (gamePlayer.role && gamePlayer.role.name === "infected_werewolf") {
				const user = gameStore.getPlayerByID(interaction.data[0]);

				chatStore.send(`Voulez-vous infecter ${user.username} ?`, "info", null, [{
					title: "Oui",
					async callback() {
						await window.JSONFetch("/interactions/use", "POST", {
							id: gameStore.currentInteractionId,
							gameId:	gameId.value,
							targetId: interaction.data[0],
							action: "infected_werewolf:skip"
						});
					},
					id: "infected_werewolf:infect"
				},
				{
					title: "Non",
					async callback() {
						await window.JSONFetch("/interactions/use", "POST", {
							id: gameStore.currentInteractionId,
							gameId:	gameId.value,
							action: "infected_werewolf:skip"
						});
					},
					id: "infected_werewolf:skip"
				}]);
			}
			break;
		}
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
			break;
		case "witch":
			player.value.classList.remove("player__witch-heal");
			player.value.classList.remove("player__witch-kill");
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
	let action = null;
	const classList = player.value.classList;

	if (classList.contains("player__witch-heal")) {
		action = "witch:revive";
	}

	if (classList.contains("player__witch-kill")) {
		action = "witch:kill";
	}

	const res = await window.JSONFetch("/interactions/use", "POST", {
		id: gameStore.currentInteractionId,
		gameId:	gameId.value,
		targetId: votedUser,
		action: action ?? gameStore.availableActions[interactionType.value]
	});

	if (res.status === 204) {
		isVoted.value = true;
	}

	if (interactionType.value === "psychic") {
		const role = await window.JSONFetch(`/roles/get/${res.data.response}`, "GET");
		chatStore.send(
			`Vous avez choisi d'espionner le rôle de ${props.player.username} qui est ${role.data.role.display_name}`,
			"success"
		);
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

const setupWitchActions = async (interaction) => {
	const actions = (await window.JSONFetch(`/interactions/actions/${gameId.value}/${gameStore.currentInteractionId}`, "GET")).data.actions;

	let actionList = [
		{
			title: "Soigner un joueur",
			callback() {
				const list = gameStore.playerRefs.filter(playerRef => interaction.data.includes(playerRef.value.dataset.id));
				for (const playerRef of list) {
					playerRef.value.classList.add("player__witch-heal");
				}
				chatStore.send("Cliquez sur un joueur pour le ressuciter", "info");
			},
			id: "witch:revive"
		},
		{
			title: "Éliminer un joueur",
			callback() {
				for (const playerRef of gameStore.playerRefs) {
					playerRef.value.classList.add("player__witch-kill");
				}
				chatStore.send("Cliquez sur un joueur pour l'éliminer", "info");
			},
			id: "witch:kill"
		},
		{
			title: "Ne rien faire",
			async callback() {
				await window.JSONFetch("/interactions/use", "POST", {
					id: gameStore.currentInteractionId,
					gameId:	gameId.value,
					action: "witch:skip"
				});
			},
			id: "witch:skip"
		}
	];

	if (interaction.data.length === 0) {
		actionList = actionList.filter(action => action.id !== "witch:revive");
	}

	actionList = actionList.filter((action) => actions.includes(action.id));

	if (gamePlayer.role && gamePlayer.role.name === "witch") {
		chatStore.send("Choisissez l'action à effectuer cette nuit", "info", null, actionList);
	}
};
</script>
