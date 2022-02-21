<template>
  <div class="player-list__wrapper">
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
import { useStore as useGameStore } from "@/stores/game.js"
import { useStore as useUserStore } from "@/stores/user.js";

export default {
  name: "PlayerList",
  components: {
    Player: Player,
    DotsSpinner: DotsSpinner
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
          console.log(users)
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
      wrapper.classList.add('player__container');

      createApp(Player, {
        player: this.injectPlayersProperties([player])[0]
      }).mount(wrapper);

      playerList.appendChild(wrapper);
    },
    removeUser (player) {
      const players = document.querySelectorAll(".player__container")
      players.forEach((playerContainer) => {
        if (parseInt(playerContainer.children[0].dataset.id) === parseInt(player.id)) {
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
