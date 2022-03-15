<template>
  <div class="play-page">
    <header class="play-page__header">
      <router-link :to="{ name: 'home_page' }">Comment jouer</router-link>
      <div class="links">
        <svg class="icon">
          <use href="/sprite.svg#wheel"></use>
        </svg>
        <button title="Se déconnecter" @click="logout()">
          <svg class="icon">
            <use href="/sprite.svg#logout"></use>
          </svg>
        </button>
      </div>
    </header>
    <div class="play-page__container">
      <div class="play-page__games">
        <header>
          <p>Liste des parties :</p>
          <button class="play-page__button" @click="openModal()">
            <i></i>
            Créer
          </button>
        </header>
        <div class="play-page__game-list">
          <GamePresentation v-for="game in games" :key="game.id" :game="game" :roles="roles"></GamePresentation>
        </div>
      </div>
      <PlayerPresentation/>
    </div>
    <NewGameModal v-if="isModalOpenned()"/>
    <footer class="play-page__footer">
      <p>&copy; Monody 2022 — Tous droits reservés.</p>
    </footer>
  </div>
</template>

<script>
import AuthService from "@/services/AuthService.js";
import NewGameModal from "@/Components/Modal/NewGameModal.vue";
import GamePresentation from "@/Components/GamePresentation.vue";
import PlayerPresentation from "@/Components/PlayerPresentation/PlayerPresentation.vue";
import {useStore} from "@/stores/modal.js";

export default {
  name: "PlayPage",
  components: {
    NewGameModal: NewGameModal,
    GamePresentation: GamePresentation,
    PlayerPresentation: PlayerPresentation
  },
  data() {
    return {
      games: [],
      roles: [],
      store: useStore()
    };
  },
  async created() {
    const res = await window.JSONFetch("/roles", "GET")

    this.roles = res.data.roles
    const games = await window.JSONFetch('/game/list', 'GET');
    if (games.data) {
      this.games = games.data.games;
    }

    Echo.channel('home').listen('.game.created', (e) => {
      this.games.push(e.data.game);
    }).listen('.game.delete', (id) => {
      this.games = this.games.filter(game => game.id !== id);
    });
  },
  methods: {
    logout() {
      const auth = new AuthService();
      auth.logout().then(() => {
        this.$router.push({name: "home_page"});
      });
    },
    openModal() {
      this.store.isOpenned = true;
    },
    isModalOpenned() {
      return this.store.isOpenned;
    },
  },
  beforeRouteLeave(to, from, next) {
    Echo.leave('home');
    next();
  }
};
</script>
