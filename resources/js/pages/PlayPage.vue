<template>
    <div class="play-page">
        <div class="play-page__play-container">
            <h1>Jouer</h1>
            <button class="play-page__logout" @click="logout()">
                Se déconnecter
            </button>
            <button @click="openModal()">Créer une partie</button>
            <div class="play-page__game-list">
                <ul>
                    <li v-for="game in games">
                        <router-link :to="{ name: 'game', params: { id: game } }">Partie n°{{ game }}</router-link>
                    </li>
                </ul>
            </div>
        </div>
        <div class="play-page__user-container">
            <img
                alt="Avatar"
                class="play-page__user-avatar"
                src="http://localhost:8000/images/avatars/1.jpg"
            />
        </div>
        <NewGameModal v-if="isModalOpenned()"/>
    </div>
</template>

<script>
import AuthService from "@/services/AuthService.js";
import NewGameModal from "@/Components/Modal/NewGameModal.vue";
import { useStore } from "@/stores/modal";

export default {
  name: "PlayPage",
  components: {
    NewGameModal: NewGameModal
  },
  data () {
    return {
      games: [],
      store: useStore()
    };
  },
  async created() {
    const games = await JSONFetch('/game/list', 'GET');
    if (games.data) {
      this.games = games.data.games;
    }

    Echo.channel('home').listen('.game.created', (e) => {
      this.games.push(e.game.id);
    });
  },
  methods: {
    logout () {
      const auth = new AuthService();
      auth.logout().then(() => {
        this.$router.push({ name: "home_page" });
      });
    },
    openModal () {
      this.store.isOpenned = true;
    },
    isModalOpenned () {
      return this.store.isOpenned;
    },
  },
  beforeRouteLeave(to, from, next) {
    Echo.leave('home');
    next();
  }
};
</script>
