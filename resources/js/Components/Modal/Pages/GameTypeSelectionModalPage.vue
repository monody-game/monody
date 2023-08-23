<template>
	<div class="game-type__wrapper">
		<p>{{ $t("new_game.game_unique_types") }}</p>
		<div class="game-type__selection-wrapper">
			<div
				class="game-type__item"
				:class="store.gameType === normal ? 'active' : ''"
				tabindex="0"
				@click="store.gameType = normal"
				@keydown.enter="store.gameType = normal"
				@keydown.space="store.gameType = normal"
			>
				<svg class="game-type__monody-icon">
					<use href="/sprite.svg#monody" />
				</svg>
				<div class="game-type__description-group">
					<h4>{{ $t("new_game.types.classic_game") }}</h4>
					<p>{{ $t("new_game.types.classic_game_desc") }}</p>
				</div>
			</div>
			<div
				class="game-type__item"
				:class="{
					active: store.gameType === vocal,
					disabled: props.hasLinked === false,
				}"
				:title="
					props.hasLinked === false ? $t('new_game.no_linked_discord') : ''
				"
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
				<div class="game-type__description-group">
					<h4>{{ $t("new_game.types.vocal_game") }}</h4>
					<p>{{ $t("new_game.types.vocal_game_desc") }}</p>
				</div>
				<p class="note">Note : {{ $t("new_game.types.vocal_game_note") }}</p>
			</div>
		</div>
		<p>{{ $t("new_game.game_combinable_types") }}</p>
		<div class="game-type__selection-wrapper">
			<div
				class="game-type__item"
				:class="store.combinablesTypes.includes(privateGame) ? 'active' : ''"
				tabindex="0"
				@click="toggle(privateGame)"
				@keydown.enter="toggle(privateGame)"
				@keydown.space="toggle(privateGame)"
			>
				<svg class="game-type__monody-icon">
					<use href="/sprite.svg#private" />
				</svg>
				<div class="game-type__description-group">
					<h4>{{ $t("new_game.types.private_game") }}</h4>
					<p>{{ $t("new_game.types.private_game_desc") }}</p>
				</div>
			</div>
			<div
				class="game-type__item"
				:class="store.combinablesTypes.includes(randomCouple) ? 'active' : ''"
				tabindex="0"
				@click="toggle(randomCouple)"
				@keydown.enter="toggle(randomCouple)"
				@keydown.space="toggle(randomCouple)"
			>
				<svg class="game-type__vocal-icon">
					<use href="/sprite.svg#random_couple" />
				</svg>
				<div class="game-type__description-group">
					<h4>{{ $t("new_game.types.random_couple_game") }}</h4>
					<p>{{ $t("new_game.types.random_couple_game_desc") }}</p>
				</div>
			</div>
			<div
				class="game-type__item"
				:class="store.combinablesTypes.includes(trouple) ? 'active' : ''"
				tabindex="0"
				@click="toggle(trouple)"
				@keydown.enter="toggle(trouple)"
				@keydown.space="toggle(trouple)"
			>
				<svg class="game-type__vocal-icon">
					<use href="/sprite.svg#trouple" />
				</svg>
				<div class="game-type__description-group">
					<h4>{{ $t("new_game.types.trouple_game") }}</h4>
					<p>{{ $t("new_game.types.trouple_game_desc") }}</p>
				</div>
			</div>
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
const privateGame = 1 << 2;
const randomCouple = 1 << 3;
const trouple = 1 << 4;
const hiddenComp = 1 << 5;

const toggle = (type) => {
	if (store.combinablesTypes.includes(type)) {
		store.combinablesTypes = store.combinablesTypes.filter(
			(combinableType) => combinableType !== type,
		);
		return;
	}

	store.combinablesTypes.push(type);
};

const store = useStore();
</script>
