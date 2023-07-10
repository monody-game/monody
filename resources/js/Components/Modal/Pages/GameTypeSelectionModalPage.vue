<template>
	<div class="game-type__wrapper">
		<div
			:class="store.type === 0x00000 ? 'active' : ''"
			tabindex="0"
			@click="store.type = 0x00000"
			@keydown.enter="store.type = 0x00000"
			@keydown.space="store.type = 0x00000"
		>
			<svg class="game-type__monody-icon">
				<use href="/sprite.svg#monody" />
			</svg>
			<h4>Partie classique</h4>
			<p>Une partie Monody classique se déroulant entièrement sur le site</p>
		</div>
		<div
			:class="{
				active: store.type === 0x00001,
				disabled: props.hasLinked === false,
			}"
			:title="
				props.hasLinked === false
					? 'Vous devez lier un compte Discord à Monody afin d\'utiliser cette fonctionnalité'
					: ''
			"
			tabindex="0"
			@click="store.type = props.hasLinked === true ? 0x00001 : 0x00000"
			@keydown.enter="store.type = props.hasLinked === true ? 0x00001 : 0x00000"
			@keydown.space="store.type = props.hasLinked === true ? 0x00001 : 0x00000"
		>
			<svg class="game-type__vocal-icon">
				<use href="/sprite.svg#vocal" />
			</svg>
			<h4>Partie vocale</h4>
			<p>Une partie Monody se déroulant sur le site et en vocal sur Discord</p>
			<p class="note">
				Note : Nécessite un compte Discord lié pour rejoindre la partie
			</p>
		</div>
	</div>
</template>

<script setup>
import { useStore } from "../../../stores/modals/game-creation-modal.js";

/**
 * Normal (site only): 0x00000
 * Voice: 0x00001
 */
const props = defineProps({
	hasLinked: {
		type: Boolean,
		required: true,
	},
});
const store = useStore();
</script>
