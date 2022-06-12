<template>
  <div class="voted-by__container">
    <img
      v-for="voter in votedBy"
      :key="voter"
      :alt="getPlayerByID(voter).username + `'s avatar`"
      :src="getAvatar(voter)"
      class="voted-by__avatar"
    >
  </div>
</template>
<script>
import { useStore } from "../../stores/game.js";

export default {
	name: "VotedBy",
	props: {
		votedBy: Object
	},
	methods: {
		getPlayerByID(id) {
			return useStore().getPlayerByID(id);
		},
		getAvatar(id) {
			return (
				"https://localhost" + this.getPlayerByID(id).avatar
			);
		},
	},
};
</script>
