<template>
	<div class="player-interaction-bubble__container" :class="color">
		<span
			v-if="
				props.type === 'vote' ||
				props.type === 'werewolves' ||
				props.type === 'mayor'
			"
		>
			<img
				v-for="voter in props.data"
				:key="voter"
				:alt="getPlayerByID(voter).username + `'s avatar`"
				:src="getAvatar(voter)"
				class="player-interaction-bubble__content"
			/>
		</span>
		<span v-else-if="props.type === 'cupid'">
			<svg class="player-interaction-bubble__content">
				<use href="/sprite.svg#heart" />
			</svg>
		</span>
		<span v-else-if="props.type === 'investigator'">
			<svg class="player-interaction-bubble__content">
				<use href="/sprite.svg#investigator" />
			</svg>
		</span>
		<svg class="player-interaction-bubble__arrow">
			<use href="/sprite.svg#arrow_down" />
		</svg>
	</div>
</template>

<script setup>
import { useStore } from "../../stores/game.js";
import { computed } from "vue";

const props = defineProps({
	type: {
		type: String,
		required: true,
		validator(value) {
			return ["vote", "werewolves", "mayor", "cupid", "investigator"].includes(
				value,
			);
		},
	},
	data: [Object, Array],
});

const store = useStore();

const color = computed(() => {
	return `player-interaction-bubble__${props.type}-color`;
});

const getPlayerByID = (id) => {
	return store.getPlayerByID(id);
};

const getAvatar = (id) => {
	return getPlayerByID(id).avatar + "?h=26&dpr=2";
};
</script>
