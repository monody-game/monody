<template>
  <div class="play-page">
    <div class="play-page__wrapper">
      <header
        v-once
        class="play-page__header"
      >
        <div class="play-page__header-title">
          <svg>
            <use href="/sprite.svg#monody" />
          </svg>
          <h2>Monody</h2>
        </div>
        <button
          @click="logout()"
        >
          Se déconnecter
          <svg class="icon">
            <use href="/sprite.svg#logout" />
          </svg>
        </button>
      </header>
      <div class="play-page__container">
        <div class="play-page__games">
          <header>
            <p>Liste des parties :</p>
            <button
              class="play-page__button btn large"
              @click="openModal()"
            >
              <svg
                width="25"
                height="25"
                viewBox="0 0 35 35"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M17.5 17.5H5.83337M17.5 29.1666V17.5V29.1666ZM17.5 17.5V5.83331V17.5ZM17.5 17.5H29.1667H17.5Z"
                  stroke="currentColor"
                  stroke-width="3"
                  stroke-linecap="round"
                />
              </svg>
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
      <Transition name="modal">
        <ProfileModal v-if="profileModalStore.isOpenned" />
      </Transition>
      <Transition name="modal">
        <ShareProfileModal v-if="shareProfileModalStore.isOpenned" />
      </Transition>
      <Footer />
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { onBeforeRouteLeave, useRouter } from "vue-router";
import { useStore } from "../stores/modals/game-creation-modal.js";
import { useStore as useModalStore } from "../stores/modals/modal.js";
import { useStore as useProfileModalStore } from "../stores/modals/profile-modal.js";
import { useStore as useShareProfileModalStore } from "../stores/modals/share-profile-modal.js";
import AuthService from "../services/AuthService.js";
import Footer from "../Components/FooterComponent.vue";
import GameCreationModal from "../Components/Modal/GameCreationModal.vue";
import GamePresentation from "../Components/GamePresentation.vue";
import PlayerPresentation from "../Components/PlayerPresentation/PlayerPresentation.vue";
import ProfileModal from "../Components/Modal/ProfileModal.vue";
import ShareProfileModal from "../Components/Modal/ShareProfileModal.vue";

const games = ref([]);
const roles = ref([]);
const store = useStore();
const router = useRouter();
const profileModalStore = useProfileModalStore();
const shareProfileModalStore = useShareProfileModalStore();

onBeforeRouteLeave(() => {
	window.Echo.leave("home");
});

const retrievedGames = await window.JSONFetch("/game/list", "GET");

if (retrievedGames.data.games.length > 0) {
	const res = await window.JSONFetch("/roles", "GET");

	roles.value = res.data.roles;
	games.value = retrievedGames.data.games;
}

window.Echo.channel("home").listen(".game-list.update", async (e) => {
	if (roles.value.length === 0) {
		const res = await window.JSONFetch("/roles", "GET");

		roles.value = res.data.roles;
	}

	games.value = e.data.games;
});

const logout = function () {
	const auth = new AuthService();
	auth.logout().then(() => {
		router.push({ name: "home_page" });
	});
};

const openModal = function () {
	useModalStore().open("game-creation-modal");
};
</script>
