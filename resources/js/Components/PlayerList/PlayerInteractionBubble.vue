<template>
  <div
    class="player-interaction-bubble__container"
    :class="color"
  >
    <span v-if="props.type === 'vote' || props.type === 'werewolf'">
      <img
        v-for="voter in props.data"
        :key="voter"
        :alt="getPlayerByID(voter).username + `'s avatar`"
        :src="getAvatar(voter)"
        class="player-interaction-bubble__content"
      >
    </span>
  </div>
</template>

<script setup>
import { useStore } from "../../stores/game.js";

const props = defineProps({
	type: {
		type: String,
		required: true,
		validator(value) {
			return ["vote", "werewolf", "psychic"].includes(value);
		}
	},
	data: [Object, Array],
});

const store = useStore();

const color = () => {
	return `player-interaction-bubble__${props.type}-color`;
};

const getPlayerByID = (id) => {
	return store.getPlayerByID(id);
};

const getAvatar = (id) => {
	return "https://localhost" + getPlayerByID(id).avatar;
};
</script>

