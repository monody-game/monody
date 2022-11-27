<template>
  <div
    class="player-interaction-bubble__container"
    :class="color"
  >
    <span v-if="props.type === 'vote' || props.type === 'werewolves'">
      <img
        v-for="voter in props.data"
        :key="voter"
        :alt="getPlayerByID(voter).username + `'s avatar`"
        :src="getAvatar(voter)"
        class="player-interaction-bubble__content"
      >
    </span>
  </div>
  <svg
    width="12"
    height="12"
    viewBox="0 0 12 12"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    class="player-interaction-bubble__arrow"
  >
    <path d="M11 0.200822L5.78486 9.16776L0.588703 0.167764L11 0.200822Z" />
  </svg>
</template>

<script setup>
import { useStore } from "../../stores/game.js";
import { computed } from "vue";

const props = defineProps({
	type: {
		type: String,
		required: true,
		validator(value) {
			return ["vote", "werewolves", "psychic"].includes(value);
		}
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
	return "https://localhost" + getPlayerByID(id).avatar;
};
</script>

