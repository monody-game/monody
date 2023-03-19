<template>
  <router-link
    :to="{ name: 'game', params: { id: props.game.id } }"
    class="game-show__container"
    @click="setGame()"
  >
    <img
      :alt="props.game.owner.username + '\'s avatar'"
      :src="props.game.owner.avatar + '?h=60&dpr=2'"
      class="game-show__avatar"
    >
    <div class="game-show__center">
      <p>{{ props.game.owner.username }}</p>
      <div class="game-show__roles">
        <div
          v-for="(count, role_id) in props.game.roles"
          :key="role_id"
          class="game-show__role"
        >
          <span
            v-if="count > 1"
            class="game-show__role-count"
          >
            {{ count }}
          </span>
          <img
            :src="props.roles.find(role => parseInt(role.id) === parseInt(role_id)).image + '?h=30&dpr=2'"
            alt=""
            class="game-show__role-image"
            :title="props.roles.find(role => parseInt(role.id) === parseInt(role_id)).display_name"
          >
        </div>
      </div>
    </div>
    <svg
      v-if="props.game.type === 1"
      title="Cette partie se déroule en vocal"
    >
      <use href="/sprite.svg#vocal" />
    </svg>
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

const setGame = async function () {
	store.roles = props.roles.filter(role => {
		return Object.keys(props.game.roles).includes(role.id.toString());
	});

	store.owner = props.game.owner;
};
</script>
