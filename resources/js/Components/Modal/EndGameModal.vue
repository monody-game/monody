<template>
	<BaseModal class="end-modal__background">
		<img
			:src="'/assets/' + (props.win ? 'victory' : 'defeat') + '.png?w=200'"
			:alt="props.win ? 'Victoire' : 'Défaite'"
			class="end-modal__image"
		/>
		<h3>
			{{
				props.win
					? `${$t("end_game.victory")} !`
					: `${$t("end_game.defeat")} ...`
			}}
		</h3>
		<p>
			{{ $t("end_game.content") }}
			<span class="bold">{{ stringifiedTeam.toLowerCase() }}</span> :
			{{
				winners.map((user) => gameStore.getPlayerByID(user).username).join(", ")
			}}
		</p>
		<p class="muted">{{ $t("end_game.close") }}</p>
	</BaseModal>
</template>

<script setup>
import BaseModal from "./BaseModal.vue";
import { useStore } from "../../stores/game.js";
import { useI18n } from "vue-i18n";

const gameStore = useStore();
const { t } = useI18n();

const props = defineProps({
	win: {
		required: true,
		type: Boolean,
	},
	winningTeam: {
		required: true,
		type: [String, Number],
	},
	winners: {
		required: true,
		type: Array,
	},
});

let stringifiedTeam = "";

if (props.winningTeam === "couple") {
	stringifiedTeam += t("end_game.couple");
} else {
	const team = await window.JSONFetch(`/team/${props.winningTeam}`, "GET");

	if (team.data.team.name !== "loners") {
		stringifiedTeam += `${t("end_game.les")} ${team.data.team.display_name}`;
	} else {
		stringifiedTeam += `${t("end_game.le")} ${
			Object.values(props.winners)[0].display_name
		}`;
	}
}
</script>
