<template>
  <div
    class="game-show__container"
    :class="userStore.discord_linked_at === null && props.game.type === 1 ? 'game-show__container-disabled' : ''"
    :title="userStore.discord_linked_at === null && props.game.type === 1 ? 'Vous devez lier votre compte Discord à Monody pour rejoindre cette partie' : ''"
    @click="openGame()"
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
  </div>
</template>

<script setup>
import { useStore } from "../stores/game.js";
import { useStore as usePopupStore } from "../stores/modals/popup.js";
import { useStore as useUserStore } from "../stores/user.js";
import { useRouter } from "vue-router";

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

const router = useRouter();
const store = useStore();
const popupStore = usePopupStore();
const userStore = useUserStore();

const getUserCount = function () {
	let total = 0;
	for (const count in props.game.roles) {
		total += parseInt(props.game.roles[count]);
	}
	return total;
};

const openGame = async function () {
	store.roles = props.roles.filter(role => {
		return Object.keys(props.game.roles).includes(role.id.toString());
	});

	store.owner = props.game.owner;

	if (props.game.type === 1) {
		popupStore.setPopup({
			warn: {
				content: "Cette partie est une partie vocale. Vous devrez rejoindre un salon vocal sur Discord, continuer ?",
				note: "Si oui, ",
				link: { name: "game", params: { id: props.game.id } },
				link_text: "cliquez ici."
			}
		});

		return;
	}

	await router.push({ name: "game", params: { id: props.game.id } });
};
</script>
