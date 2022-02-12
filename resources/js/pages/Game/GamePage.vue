<template>
  <div class="game-page__container day">
    <div class="game-page__header">
      <a class="game-page__home-link" @click.prevent="disconnect()">
        <svg
          fill="none"
          height="40"
          viewBox="0 0 40 40"
          width="40"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M37.4556 19.2111L20.789 2.54445C20.5808 2.3375 20.2992 2.22134 20.0056 2.22134C19.7121 2.22134 19.4305 2.3375 19.2223 2.54445L2.55563 19.2111C2.3736 19.4237 2.27848 19.6971 2.28928 19.9767C2.30008 20.2564 2.41601 20.5216 2.61389 20.7195C2.81177 20.9174 3.07704 21.0333 3.35668 21.0441C3.63632 21.0549 3.90974 20.9598 4.12229 20.7778L20.0001 4.9L35.8778 20.7889C36.0904 20.9709 36.3638 21.066 36.6435 21.0552C36.9231 21.0444 37.1884 20.9285 37.3862 20.7306C37.5841 20.5327 37.7001 20.2675 37.7109 19.9878C37.7217 19.7082 37.6265 19.4348 37.4445 19.2222L37.4556 19.2111Z"
            fill="white"
          />
          <path
            d="M31.1111 35.5556H25.5555V24.4444H14.4444V35.5556H8.88885V20L6.66663 22.2222V35.5556C6.66663 36.1449 6.90075 36.7102 7.3175 37.1269C7.73425 37.5437 8.29948 37.7778 8.88885 37.7778H16.6666V26.6667H23.3333V37.7778H31.1111C31.7004 37.7778 32.2657 37.5437 32.6824 37.1269C33.0992 36.7102 33.3333 36.1449 33.3333 35.5556V21.9556L31.1111 19.7333V35.5556Z"
            fill="white"
          />
        </svg>
        <p>Accueil</p>
      </a>
      <Counter/>
    </div>
    <div class="game-page__main">
      <Chat/>
      <DotsSpinner v-if="loading"/>
      <PlayerList :token="token"/>
    </div>
  </div>
</template>

<script>
import Counter from "@/Components/Counter.vue";
import Chat from "@/Components/Chat/Chat.vue";
import PlayerList from "@/Components/PlayerList/PlayerList.vue";
import DotsSpinner from "@/Components/Spinners/DotsSpinner.vue";
import { useStore } from "@/stores/game.js"

export default {
  name: "GamePage",
  components: {
    Counter: Counter,
    Chat: Chat,
    PlayerList: PlayerList,
    DotsSpinner: DotsSpinner
  },
  mounted () {
    this.loading = true;
    this.emitConnect();
    this.loading = false;

    window.addEventListener("beforeunload", (event) => {
      if (this.isStarted) {
        event.preventDefault();
        event.returnValue = "";
      }
      Echo.leave(`game.${this.gameId}`);
    });
  },
  data () {
    return {
      gameId: this.$route.params.id,
      token: "",
      loading: false,
      isStarted: false,
      store: useStore()
    };
  },
  methods: {
    getToken: async function () {
      const response = await fetch("/api/game/token", {
        method: "GET",
        headers: {
          Authorization:
            "Bearer " + localStorage.getItem('access-token'),
        },
      });
      const data = await response.json();
      this.token = data.token;
    },
    emitConnect: async function () {
      await this.getToken();
      Echo.join(`game.${this.gameId}`)
        .here((users) => {
          console.log('here' ,users)
          //this.$store.commit("setGamePlayers", users);
        })
        .joining((user) => {
          console.log('joining', user)
          //this.$store.commit("addGamePlayer", user);
        })
        .leaving((user) => {
          console.log('leaving', user)
          //this.$store.commit("removeGamePlayer", user);
        })
    },
    disconnect: async function () {
      await this.$router.push("/play");
    },
  },
  async beforeRouteLeave (to, from, next) {
    Echo.leave(`game.${this.gameId}`);
    this.store.playerList = [];
    next();
  }
};
</script>

<style scoped></style>
