<template>
  <div class="play-page">
    <header class="play-page__header">
      <router-link :to="{ name: 'home_page' }">
        Comment jouer
      </router-link>
      <div class="links">
        <router-link
          to="profile"
          aria-label="Profil"
        >
          <svg
            class="icon"
            tabindex="0"
          >
            <use href="/sprite.svg#wheel" />
          </svg>
        </router-link>
        <button
          title="Se déconnecter"
          @click="logout()"
        >
          <svg class="icon">
            <use href="/sprite.svg#logout" />
          </svg>
        </button>
      </div>
    </header>
    <div class="play-page__container">
      <div class="play-page__games">
        <header>
          <p>Liste des parties :</p>
          <button
            class="play-page__button btn large"
            @click="openModal()"
          >
            <i />
            Créer
          </button>
        </header>
        <div class="play-page__game-list">
          <GamePresentation
            v-for="game in games"
            :key="game.id"
            :game="game"
            :roles="roles"
          />
        </div>
      </div>
      <PlayerPresentation />
    </div>
    <Transition name="modal">
      <GameCreationModal v-if="store.isOpenned" />
    </Transition>
    <Footer />
  </div>
</template>

<script setup>
import AuthService from "../services/AuthService.js";
import Footer from "../Components/FooterComponent.vue";
import GameCreationModal from "../Components/Modal/GameCreationModal.vue";
import GamePresentation from "../Components/GamePresentation.vue";
import PlayerPresentation from "../Components/PlayerPresentation/PlayerPresentation.vue";
import { useStore as useGameCreationModal } from "../stores/GameCreationModal";
import { onBeforeRouteLeave, useRouter } from "vue-router";
import { ref } from "vue";

const games = ref([]);
const roles = ref([]);
const store = useGameCreationModal();
const router = useRouter();

onBeforeRouteLeave((to, from, next) => {
	window.Echo.leave("home");
	next();
});

const retrievedGames = await window.JSONFetch("/game/list", "GET");

if (retrievedGames.data.games.length > 0) {
	const res = await window.JSONFetch("/roles", "GET");

	roles.value = res.data.roles;
	games.value = retrievedGames.data.games;
}

window.Echo.channel("home").listen(".game.created", async (e) => {
	if (roles.value.length === 0) {
		const res = await window.JSONFetch("/roles", "GET");

		roles.value = res.data.roles;
	}

	games.value.push(e.data.game);
}).listen(".game.delete", (id) => {
	games.value = games.value.filter(game => game.id !== id);
});

const logout = function () {
	const auth = new AuthService();
	auth.logout().then(() => {
		router.push({ name: "home_page" });
	});
};

const openModal = function () {
	store.isOpenned = true;
};
</script>
