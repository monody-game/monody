<template>
  <div class="player-list__wrapper">
    <Spinner v-if="loading"/>
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
import Spinner from "@/Components/Spinner.vue";
import { useStore as useGameStore } from "@/stores/game.js"
import { useStore as useUserStore } from "@/stores/user.js";

export default {
  name: "PlayerList",
  components: {
    Player: Player,
    Spinner: Spinner
  },
  data () {
    return {
      playerList: [],
      loading: false,
      gameStore: useGameStore(),
      userStore: useUserStore()
    };
  },
  mounted () {
    (async () => {
      Echo.join(`game.${this.$route.params.id}`)
        .here((users) => {
          users.forEach((user) => {
            this.addUser(user);
          });
        })
        .joining((user) => {
          this.addUser(user)
        })
        .leaving((user) => {
          this.removeUser(user)
        })
    })();
  },
  methods: {
    addUser (player) {
      const playerList = document.querySelector(".player-list__wrapper");
      const wrapper = document.createElement("div");
      player = this.injectPlayersProperties([player])[0]

      createApp(Player, {
        player: player
      }).mount(wrapper);

      this.gameStore.playerList.push(player)

      playerList.appendChild(wrapper);
    },
    removeUser (player) {
      const players = document.querySelector(".player-list__wrapper")
      Array.from(players.children).forEach((playerContainer) => {
        if (parseInt(playerContainer.children[0].dataset.id) === parseInt(player.id)) {
          this.gameStore.playerList = this.gameStore.playerList.filter((p) => p.id !== player.id)
          playerContainer.remove();
        }
      });
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
