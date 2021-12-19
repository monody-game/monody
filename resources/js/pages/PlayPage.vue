<template>
  <div class="play-page">
    <div class="play-page__play-container">
      <h1>Jouer</h1>
      <button class="play-page__logout" @click="logout()">
        Se déconnecter
      </button>
      <button @click="openModal()">Créer une partie</button>
      <router-link :to="{ name: 'game', params: { id: 1 } }">Partie n°1</router-link>
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

export default {
  name: "PlayPage",
  components: {
    NewGameModal: NewGameModal
  },
  created () {
    const auth = new AuthService();
    if (
      !auth.check(this.$store) ||
      auth.getUserIfAccessToken(this.$store) === false
    ) {
      this.$router.push({ name: "login" });
    }
  },
  data () {
    return {
      user: this.$store.getUser,
    };
  },
  methods: {
    logout () {
      const auth = new AuthService();
      auth.logout(this.$store).then(() => {
        this.$router.push({ name: "home_page" });
      });
    },
    openModal () {
      this.$store.commit("openModal");
    },
    isModalOpenned () {
      return this.$store.getters.isModalOpenned;
    },
  },
};
</script>
