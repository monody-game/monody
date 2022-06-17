<template>
  <router-link
    :to="{ name: 'game', params: { id: game.id } }"
    class="game-show__container"
  >
    <img
      :alt="game.owner.username + '\'s avatar'"
      :src="game.owner.avatar"
      class="game-show__avatar"
    >
    <div class="game-show__center">
      <p>{{ game.owner.username }}</p>
      <div class="game-show__roles">
        <img
          v-for="role_id in Object.keys(game.roles)"
          :key="role_id"
          :src="roles.find(role => parseInt(role.id) === parseInt(role_id)).image"
          alt=""
          class="game-show__role"
        >
      </div>
    </div>
    <p>{{ game.users.length }}/{{ getUserCount() }}</p>
  </router-link>
</template>

<script>
export default {
	name: "GamePresentation",
	props: {
		game: Object,
		roles: Array
	},
	methods: {
		getUserCount() {
			let total = 0;
			for (const count in this.game.roles) {
				total += parseInt(this.game.roles[count]);
			}
			return total;
		}
	}
};
</script>
