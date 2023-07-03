<template>
  <BaseModal class="end-modal__background">
    <img
      :src="'/assets/' + (props.win ? 'victory' : 'defeat') + '.png?w=200'"
      :alt="props.win ? 'Victoire' : 'Défaite'"
      class="end-modal__image"
    >
    <h3>{{ props.win ? 'Victoire !' : 'Défaite ...' }}</h3>
    <p>La victoire a été remportée par <span class="bold">{{ stringifiedTeam.toLowerCase() }}</span> : {{ winners.map(user => gameStore.getPlayerByID(user).username).join(", ") }}</p>
    <p class="muted">
      Cliquez n'importe où pour fermer cette fenêtre
    </p>
  </BaseModal>
</template>

<script setup>
import BaseModal from "./BaseModal.vue";
import { useStore } from "../../stores/game.js";

const gameStore = useStore();

const props = defineProps({
	win: {
		required: true,
		type: Boolean
	},
	winningTeam: {
		required: true,
		type: Number,
	},
	winners: {
		required: true,
		type: Array
	}
});

let stringifiedTeam = "";

if (props.winningTeam === "couple") {
	stringifiedTeam += "le couple.";
} else {
	const team = await window.JSONFetch(`/team/${props.winningTeam}`, "GET");

	if (team.data.team.name !== "loners") {
		stringifiedTeam += `les ${team.data.team.display_name}`;
	} else {
		stringifiedTeam += `le ${Object.values(props.winners)[0].display_name}`;
	}
}

</script>
