<template>
	<div
		ref="player"
		:data-id="props.player.id"
		class="player__container"
		:data-is-dead="isDead"
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
			/>
			<div v-if="isDead === true" class="player__is-dead">
				<span class="player__is-dead-shadow" />
				<svg>
					<use href="/sprite.svg#death" />
				</svg>
			</div>
			<div class="player__badges">
				<span
					v-if="isMayor === true"
					:title="$t('player.mayor')"
					class="player__is-mayor"
				>
					<svg>
						<use href="/sprite.svg#mayor" />
					</svg>
				</span>
				<span
					v-if="isWerewolf === true"
					:title="$t('player.werewolf')"
					class="player__is-wolf"
				/>
				<span
					v-if="isPaired === true"
					:title="$t('player.couple')"
					class="player__is-paired"
				>
					<svg>
						<use href="/sprite.svg#heart" />
					</svg>
				</span>
				<span
					v-if="isGuarded === true"
					:title="$t('player.guarded')"
					class="player__is-guarded"
				>
					<svg>
						<use href="/sprite.svg#guard" />
					</svg>
				</span>
				<span
					v-if="isContaminated === true"
					:title="$t('player.contaminated')"
					class="player__is-contaminated"
				>
					<svg>
						<use href="/sprite.svg#parasite" />
					</svg>
				</span>
				<span
					v-if="isTargeted === true"
					:title="$t('player.target')"
					class="player__is-target"
				>
					<svg>
						<use href="/sprite.svg#target" />
					</svg>
				</span>
			</div>
			<span
				v-if="isDisconnected === true"
				:title="$t('player.disconnected')"
				class="player__is-disconnected"
			>
				<svg>
					<use href="/sprite.svg#websockets" />
				</svg>
			</span>
			<span
				v-if="isCompared === true"
				:title="$t('player.compared')"
				class="player__is-compared"
			>
				<svg>
					<use href="/sprite.svg#investigator_crossed" />
				</svg>
			</span>
			<span
				v-if="playerRole !== null"
				:title="playerRole.display_name"
				class="player__role"
			>
				<img :src="playerRole.image" alt="" />
			</span>
		</div>
		<p
			class="player__username"
			:data-is-owner="isOwner"
			:title="isOwner ? $t('player.owner') : ''"
		>
			<svg v-if="isOwner === true">
				<use href="/sprite.svg#crown" />
			</svg>
			{{ props.player.username }}
		</p>
	</div>
</template>

<script setup>
import { computed, nextTick, onMounted, ref } from "vue";
import { useStore as useGameStore } from "../../stores/game.js";
import { useStore as useUserStore } from "../../stores/user.js";
import { useStore as useChatStore } from "../../stores/chat.js";
import PlayerInteractionBubble from "./PlayerInteractionBubble.vue";
import { useI18n } from "vue-i18n";

const props = defineProps({
	player: {
		type: Object,
		required: true,
	},
});

const gameStore = useGameStore();
const userStore = useUserStore();
const chatStore = useChatStore();
const { t } = useI18n();

const isVoted = ref(false);
const isDead = ref(false);
const isMayor = ref(false);
const isWerewolf = ref(false);
const isTargeted = ref(false);
const isContaminated = ref(false);
const isPaired = ref(false);
const isGuarded = ref(false);
const isDisconnected = ref(false);
const isOwner = ref(false);
const isCompared = ref(false);

const playerRole = ref(null);
const votedBy = ref(props.player.voted_by);
const interactionType = ref("");
const player = ref();
const gamePlayer = gameStore.getPlayerByID(props.player.id);

nextTick(() => {
	gameStore.playerRefs.push(player);
});

gameStore.$subscribe((mutation, state) => {
	if (state.dead_users.includes(props.player.id)) {
		isDead.value = true;
	}

	if (state.werewolves.includes(props.player.id)) {
		isWerewolf.value = true;
	}

	if (Object.keys(state.voted_users).includes(props.player.id)) {
		isVoted.value = true;
		votedBy.value = gameStore.voted_users[props.player.id];
	}

	if (state.mayor === props.player.id) {
		isMayor.value = true;
	}

	if (state.angel_target === props.player.id) {
		isTargeted.value = true;
	}

	if (state.contaminated.includes(props.player.id)) {
		isContaminated.value = true;
	}

	if (state.couple.includes(props.player.id)) {
		isPaired.value = true;
	}

	if (state.owner.id === props.player.id) {
		isOwner.value = true;
	}

	interactionType.value = state.interactionType;
});

const gameId = computed(() => {
	return document.URL.split("/").at(-1);
});

const userID = computed(() => {
	return userStore.id;
});

window.Echo.join(`game.${gameId.value}`)
	.joining((user) => {
		if (user.id === props.player.id) {
			isDisconnected.value = false;
		}
	})
	.listen(".list.disconnect", (user) => {
		if (user.user_id === props.player.id) {
			isDisconnected.value = true;
		}
	})
	.listen(".interaction.open", ({ interaction }) => {
		interactionType.value = interaction.type;
		gameStore.interactionType = interaction.type;
		gameStore.currentInteractionId = interaction.id;

		if (
			gameStore.dead_users.includes(userStore.id) &&
			interaction.type !== "hunter"
		) {
			return;
		}

		switch (interaction.type) {
			case "vote":
			case "werewolves":
			case "white_werewolf":
				if (isDead.value === false) {
					player.value.classList.add("player__votable");
				}
				break;
			case "hunter":
				if (gamePlayer.role && gamePlayer.role.name === "hunter") {
					player.value.classList.add("player__hover-disabled");
					break;
				}

				player.value.classList.add("player__votable");
				break;
			case "mayor":
				if (isDead.value === false) {
					player.value.classList.add("player__electable");
				}
				break;
			case "guard":
				isGuarded.value = false;

				if (gamePlayer.role && gamePlayer.role.name === "guard") {
					chatStore.send(t("player.guard"), "info");
				}

				if (isDead.value === false && props.player.id !== interaction.data) {
					player.value.classList.add("player__guardable");
				}
				break;
			case "psychic":
				if (gamePlayer.role && gamePlayer.role.name === "psychic") {
					chatStore.send(t("player.spec"), "info");
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

					chatStore.send(t("player.infect", [user.username]), "info", null, [
						{
							title: t("player.yes"),
							async callback() {
								await window.JSONFetch("/interactions/use", "POST", {
									id: gameStore.currentInteractionId,
									gameId: gameId.value,
									targetId: interaction.data[0],
									action: "infected_werewolf:infect",
								});
							},
							id: "infected_werewolf:infect",
						},
						{
							title: t("player.no"),
							async callback() {
								await window.JSONFetch("/interactions/use", "POST", {
									id: gameStore.currentInteractionId,
									gameId: gameId.value,
									action: "infected_werewolf:skip",
								});
							},
							id: "infected_werewolf:skip",
						},
					]);
				}
				break;
			case "surly_werewolf":
				if (isDead.value === false) {
					player.value.classList.add("player__votable");
				}

				if (gamePlayer.role && gamePlayer.role.name === "surly_werewolf") {
					chatStore.send(t("player.bite"), "info", null, [
						{
							title: t("player.skip"),
							async callback() {
								await window.JSONFetch("/interactions/use", "POST", {
									id: gameStore.currentInteractionId,
									gameId: gameId.value,
									action: "surly_werewolf:skip",
								});
							},
							id: "surly_werewolf:skip",
						},
					]);
				}
				break;
			case "parasite":
				if (isDead.value === false && userStore.id !== props.player.id) {
					player.value.classList.add("player__parasite-hover");
				}

				if (userStore.id === props.player.id) {
					chatStore.send(t("player.contaminate"), "info");
					player.value.classList.add("player__hover-disabled");
				}
				break;
			case "cupid":
				player.value.classList.add("player__pairable");
				break;
			case "investigator":
				if (gamePlayer.role && gamePlayer.role.name === "investigator") {
					chatStore.send(t("player.investigator"), "info");
				}
				const isNotComparable = Object.values(
					interaction.data.not_comparable,
				).includes(props.player.id);

				if (isNotComparable) {
					isCompared.value = true;
				}

				if (isDead.value === false && !isNotComparable) {
					player.value.classList.add("player__comparable");
				} else {
					player.value.classList.add("player__hover-disabled");
				}
				break;
		}
	})
	.listen(".interaction.close", () => {
		if (player.value) {
			player.value.classList.remove(
				"player__votable",
				"player__electable",
				"player__psychic-hover",
				"player__witch-heal",
				"player__witch-kill",
				"player__parasite-hover",
				"player__pairable",
				"player__guardable",
				"player__disabled",
				"player__hover-disabled",
				"player__comparable",
			);
		}

		votedBy.value = [];
		isVoted.value = false;
		gameStore.currentInteractionId = "";
	})
	.listen(".interaction.vote", ({ data }) => addVote(data))
	.listen(".interaction.werewolves:kill", ({ data }) => addVote(data))
	.listen(".interaction.cupid:pair", ({ data }) => {
		const pairArray = data.payload.votedPlayers;
		votedBy.value = [];
		isVoted.value = false;

		if (
			Object.values(pairArray).length > 0 &&
			Object.values(pairArray)[0].includes(props.player.id)
		) {
			votedBy.value = props.player;
			isVoted.value = true;
		}
	})
	.listen(".interaction.investigator:compare", ({ data }) => {
		const pairArray = data.payload.comparedPlayers;
		votedBy.value = [];
		isVoted.value = false;

		if (
			Object.values(pairArray).length > 0 &&
			Object.values(pairArray)[0].includes(props.player.id)
		) {
			votedBy.value = props.player;
			isVoted.value = true;
		}
	})
	.listen(".game.end", () => {
		isVoted.value = false;
	})
	.listen(".game.state", (state) => {
		if (state.skipped === false) {
			votedBy.value = [];
			isVoted.value = false;
		}
	});

const send = async function (votingUser, votedUser) {
	if (!gameStore.currentInteractionId) {
		return;
	}

	let action = null;
	const classList = player.value.classList;

	if (classList.contains("player__witch-heal")) {
		action = "witch:revive";
	}

	if (classList.contains("player__witch-kill")) {
		action = "witch:kill";
	}

	if (
		classList.contains("player__votable") &&
		interactionType.value === "surly_werewolf"
	) {
		action = "surly_werewolf:bite";
	}

	const res = await window.JSONFetch("/interactions/use", "POST", {
		id: gameStore.currentInteractionId,
		gameId: gameId.value,
		targetId: votedUser,
		action: action ?? gameStore.availableActions[interactionType.value],
	});

	if (res.status === 204) {
		isVoted.value = true;
	}

	if (interactionType.value === "psychic") {
		const role = await window.JSONFetch(
			`/roles/get/${res.data.interaction.response}`,
			"GET",
		);
		playerRole.value = role.data.role;
		chatStore.send(
			t("player.spec_response", {
				username: props.player.username,
				role: role.data.role.display_name,
			}),
			"success",
		);
	}

	if (interactionType.value === "surly_werewolf") {
		chatStore.send(
			t("player.bite_response", [props.player.username]),
			"success",
		);
	}

	if (interactionType.value === "parasite") {
		gameStore.contaminated.push(votedUser);
	}

	if (interactionType.value === "guard" && res.ok) {
		isGuarded.value = votedUser === props.player.id;
	}

	if (
		interactionType.value === "investigator" &&
		res.data.interaction.response !== null
	) {
		chatStore.send(
			t(`player.compare_response_${res.data.interaction.response}`),
			res.data.interaction.response ? "success" : "warn",
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
	const actions = (
		await window.JSONFetch(
			`/interactions/actions/${gameId.value}/${gameStore.currentInteractionId}`,
			"GET",
		)
	).data.actions;

	let actionList = [
		{
			title: t("player.revive"),
			callback() {
				const list = gameStore.playerRefs.filter((playerRef) =>
					interaction.data.includes(playerRef.value.dataset.id),
				);
				for (const playerRef of list) {
					playerRef.value.classList.add("player__witch-heal");
				}
				chatStore.send(t("player.revive_desc"), "info");
			},
			id: "witch:revive",
		},
		{
			title: t("player.kill"),
			callback() {
				for (const playerRef of gameStore.playerRefs) {
					playerRef.value.classList.add("player__witch-kill");
				}
				chatStore.send(t("player.kill_desc"), "info");
			},
			id: "witch:kill",
		},
		{
			title: t("player.skip"),
			async callback() {
				await window.JSONFetch("/interactions/use", "POST", {
					id: gameStore.currentInteractionId,
					gameId: gameId.value,
					action: "witch:skip",
				});
			},
			id: "witch:skip",
		},
	];

	if (interaction.data.length === 0) {
		actionList = actionList.filter((action) => action.id !== "witch:revive");
	}

	actionList = actionList.filter((action) => actions.includes(action.id));

	if (gamePlayer.role && gamePlayer.role.name === "witch") {
		chatStore.send(t("player.choose_action"), "info", null, actionList);
	}
};
</script>
