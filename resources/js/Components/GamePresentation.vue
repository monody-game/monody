<template>
  <router-link
    :to="{ name: 'game', params: { id: props.game.id } }"
    class="game-show__container"
    @click="setRoles"
  >
    <img
      :alt="props.game.owner.username + '\'s avatar'"
      :src="props.game.owner.avatar + '?h=60&dpr=2'"
      class="game-show__avatar"
    >
    <div class="game-show__center">
      <p>{{ props.game.owner.username }}</p>
      <div class="game-show__roles">
        <img
          v-for="role_id in Object.keys(props.game.roles)"
          :key="role_id"
          :src="props.roles.find(role => parseInt(role.id) === parseInt(role_id)).image + '?h=30&dpr=2'"
          alt=""
          class="game-show__role"
          :title="props.roles.find(role => parseInt(role.id) === parseInt(role_id)).display_name"
        >
      </div>
    </div>
    <p>{{ props.game.users.length }} / {{ getUserCount() }}</p>
  </router-link>
</template>

<script setup>
import { useStore } from "../stores/game.js";

const props = defineProps({
	game: {
		type: Object,
		required: true
	},
	roles: {
		type: Array,
		required: true
	}
});
const store = useStore();

const getUserCount = function () {
	let total = 0;
	for (const count in props.game.roles) {
		total += parseInt(props.game.roles[count]);
	}
	return total;
};

const setRoles = async function () {
	store.roles = props.roles.filter(role => {
		return Object.keys(props.game.roles).includes(role.id.toString());
	});
};
</script>
