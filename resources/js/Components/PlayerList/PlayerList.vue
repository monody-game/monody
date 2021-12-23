<template>
  <div :data-token="token" class="player-list__wrapper">
    <DotsSpinner v-if="loading"/>
    <Player
      v-for="player in playerList"
      :key="player.id"
      :player="player"
      :socket="socket"
    />
  </div>
</template>

<script>
import Vue from "vue";
import Player from "@/Components/PlayerList/Player.vue";
import DotsSpinner from "@/Components/Spinners/DotsSpinner";

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
      loading: false
    };
  },
  mounted () {
    (async () => {
      Echo.private(`game.${this.$route.params.id}`).listen("game.users.new", ({ user }) => {
        this.$store.commit("addGamePlayer", this.injectPlayersProperties([user])[0]);
      }).listen("game.users", ({ users }) => {
        this.loading = true;
        const list = this.injectPlayersProperties(users);
        this.$store.commit("setGamePlayers", list);
        this.playerList = list;
        this.loading = false;
      }).listen("game.users.leave", ({ user }) => {
        const gameUser = this.injectPlayersProperties([user])[0];
        this.$store.commit("removeGamePlayer", gameUser);
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
      const PlayerClass = Vue.extend(Player);
      const instance = new PlayerClass({
        propsData: { player: player },
      });
      instance.$mount();
      playerList.appendChild(instance.$el);
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
