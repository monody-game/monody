<template>
	<div class="game-type__wrapper">
		<div
			:class="store.gameType === normal ? 'active' : ''"
			tabindex="0"
			@click="store.gameType = normal"
			@keydown.enter="store.gameType = normal"
			@keydown.space="store.gameType = normal"
		>
			<svg class="game-type__monody-icon">
				<use href="/sprite.svg#monody" />
			</svg>
			<h4>{{ $t("new_game.classic_game") }}</h4>
			<p>{{ $t("new_game.classic_game_desc") }}</p>
		</div>
		<div
			:class="{
				active: store.gameType === vocal,
				disabled: props.hasLinked === false,
			}"
			:title="props.hasLinked === false ? $t('new_game.no_linked_discord') : ''"
			tabindex="0"
			@click="store.gameType = props.hasLinked === true ? vocal : normal"
			@keydown.enter="
				store.gameType = props.hasLinked === true ? vocal : normal
			"
			@keydown.space="
				store.gameType = props.hasLinked === true ? vocal : normal
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
 * Normal (site only): 0x1 (1<<0)
 * Vocal: 0x2 (1<<1)
 */
const props = defineProps({
	hasLinked: {
		type: Boolean,
		required: true,
	},
});

const normal = 1 << 0;
const vocal = 1 << 1;

const store = useStore();
</script>
