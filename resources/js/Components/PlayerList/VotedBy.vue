<template>
  <div class="voted-by__container">
    <img
      v-for="voter in props.votedBy"
      :key="voter"
      :alt="getPlayerByID(voter).username + `'s avatar`"
      :src="getAvatar(voter)"
      class="voted-by__avatar"
    >
  </div>
</template>
<script setup>
import { useStore } from "../../stores/game.js";

const props = defineProps({
	votedBy: {
		type: Object,
		required: true
	}
});
const store = useStore();

const getPlayerByID = function (id) {
	return store.getPlayerByID(id);
};

const getAvatar = function (id) {
	return "https://localhost" + this.getPlayerByID(id).avatar;
};
</script>
