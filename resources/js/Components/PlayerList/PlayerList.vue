<template>
  <div :data-token="token" class="player-list__wrapper">
    <DotsSpinner v-if="loading"/>
    <Player
      v-for="player in playerList"
      :key="player.id"
      :player="player"
    />
  </div>
</template>

<script>
import { createApp } from "vue";
import Player from "@/Components/PlayerList/Player.vue";
import DotsSpinner from "@/Components/Spinners/DotsSpinner.vue";
import { useStore } from "@/stores/game.js"

export default {
  name: "PlayerList",
  props: ["token"],
  components: {
    Player: Player,
    DotsSpinner: DotsSpinner
  },
  data () {
    return {
      playerList: [],
      loading: false,
      store: useStore()
    };
  },
  mounted () {
    (async () => {
      Echo.join(`game.${this.$route.params.id}`).listen("game.users.new", ({ user }) => {
        this.store.playerList.push(this.injectPlayersProperties([user])[0]);
      }).listen("game.users", ({ users }) => {
        this.loading = true;
        const list = this.injectPlayersProperties(users);
        this.store.playerList = list;
        this.playerList = list;
        this.loading = false;
      }).listen("game.users.leave", ({ user }) => {
        const gameUser = this.injectPlayersProperties([user])[0];
        this.store.removeGamePlayer(gameUser);
      });
    })();
  },
  computed: {
    gameId () {
      return window.location.pathname.match("[0-9]$")[0];
    },
  },
  methods: {
    addUser (player) {
      const playerList = document.querySelector(".player-list__wrapper");
      createApp(Player, {
        propsData: {
          player: player,
          socket: this.socket
        }
      }).mount(playerList);
    },
    injectPlayersProperties (players) {
      players.forEach((player) => {
        player.voted_by = [];
        player.role = {
          group: "villager",
          name: "villager",
          see_has: "villager",
        };
      });
      return players;
    },
  },
};
</script>

<style scoped></style>
