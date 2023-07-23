<template>
	<div class="game-type__wrapper">
		<div
			:class="store.gameType === 0b00001 ? 'active' : ''"
			tabindex="0"
			@click="store.gameType = 0b00001"
			@keydown.enter="store.gameType = 0b00001"
			@keydown.space="store.gameType = 0b00001"
		>
			<svg class="game-type__monody-icon">
				<use href="/sprite.svg#monody" />
			</svg>
			<h4>{{ $t("new_game.classic_game") }}</h4>
			<p>{{ $t("new_game.classic_game_desc") }}</p>
		</div>
		<div
			:class="{
				active: store.gameType === 0b00010,
				disabled: props.hasLinked === false,
			}"
			:title="props.hasLinked === false ? $t('new_game.no_linked_discord') : ''"
			tabindex="0"
			@click="store.gameType = props.hasLinked === true ? 0b00010 : 0b00001"
			@keydown.enter="
				store.gameType = props.hasLinked === true ? 0b00010 : 0b00001
			"
			@keydown.space="
				store.gameType = props.hasLinked === true ? 0b00010 : 0b00001
			"
		>
			<svg class="game-type__vocal-icon">
				<use href="/sprite.svg#vocal" />
			</svg>
			<h4>{{ $t("new_game.vocal_game") }}</h4>
			<p>{{ $t("new_game.vocal_game_desc") }}</p>
			<p class="note">Note : {{ $t("new_game.vocal_game_note") }}</p>
		</div>
	</div>
</template>

<script setup>
import { useStore } from "../../../stores/modals/game-creation-modal.js";

/**
 * Normal (site only): 0b00001
 * Voice: 0b00010
 */
const props = defineProps({
	hasLinked: {
		type: Boolean,
		required: true,
	},
});
const store = useStore();
</script>
