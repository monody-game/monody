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
            class="play-page__button"
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
    <footer class="play-page__footer">
      <p>&copy; Monody 2022 — Tous droits reservés.</p>
    </footer>
  </div>
</template>

<script>
import AuthService from "../services/AuthService.js";
import GameCreationModal from "../Components/Modal/GameCreationModal.vue";
import GamePresentation from "../Components/GamePresentation.vue";
import PlayerPresentation from "../Components/PlayerPresentation/PlayerPresentation.vue";
import { useStore as useGameCreationModal } from "../stores/GameCreationModal";

export default {
	name: "PlayPage",
	components: {
		GameCreationModal: GameCreationModal,
		GamePresentation: GamePresentation,
		PlayerPresentation: PlayerPresentation
	},
	beforeRouteLeave(to, from, next) {
		window.Echo.leave("home");
		next();
	},
	data() {
		return {
			games: [],
			roles: [],
			store: useGameCreationModal()
		};
	},
	async created() {
		const res = await window.JSONFetch("/roles", "GET");

		this.roles = res.data.roles;
		const games = await window.JSONFetch("/game/list", "GET");
		if (games.data) {
			this.games = games.data.games;
		}

		window.Echo.channel("home").listen(".game.created", (e) => {
			this.games.push(e.data.game);
		}).listen(".game.delete", (id) => {
			this.games = this.games.filter(game => game.id !== id);
		});
	},
	methods: {
		logout() {
			const auth = new AuthService();
			auth.logout().then(() => {
				this.$router.push({ name: "home_page" });
			});
		},
		openModal() {
			this.store.isOpenned = true;
		},
	}
};
</script>
