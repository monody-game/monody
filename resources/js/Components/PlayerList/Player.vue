<template>
  <div
    :data-id="player.id"
    class="player__container player__votable"
    @click="vote(userID, player.id)"
  >
    <VotedBy v-if="isVoted" :player="player" :votedBy="votedBy"/>
    <div class="player__avatar-container">
      <img
        :alt="player.username + `'s avatar`"
        :class="isVoted === true ? 'player__is-voted' : ''"
        :src="getAvatar"
        class="player__avatar"
      />
      <span v-if="this.player.role.group === 'werewolf'" class="player__is-wolf"></span>
    </div>
    <p class="player__username">{{ player.username }}</p>
  </div>
</template>

<script>
import VotedBy from "@/Components/PlayerList/VotedBy.vue";
import {useStore as useGameStore} from "@/stores/game.js";
import {useStore as useUserStore} from "@/stores/user.js";

export default {
  name: "Player",
  props: ["player"],
  components: {VotedBy},
  data() {
    return {
      isVoted: this.player.voted_by.length > 1,
      gameStore: useGameStore(),
      userStore: useUserStore(),
      gameId: document.URL.split('/')[document.URL.split('/').length - 1]
    };
  },
  computed: {
    userID() {
      return this.userStore.id;
    },
    votedBy() {
      return this.player.voted_by;
    },
    getAvatar() {
      return "http://localhost:8000" + this.player.avatar;
    }
  },
  methods: {
    vote: function (voted_by, voted_user, emit_event = true) {
      if (typeof voted_by === "undefined") {
        return;
      }

      if (this.votedBy.includes(voted_by)) {
        this.unVote(voted_by, voted_user);
        return;
      }

      if (
        this.gameStore.getCurrentVote > 0
      ) {
        this.unVote(voted_by, this.gameStore.getCurrentVote);
        return;
      }

      this.isVoted = true;

      this.gameStore.setVote({
        userID: voted_user,
        votedBy: voted_by,
      });

      if (emit_event) {
        Echo.join(`game.${this.gameId}`).whisper("game.voting", {
          voted_user: voted_user,
          voted_by: voted_by,
        });
      }
    },
    unVote: function (voted_by, voted_user, emit_event = true) {
      if (typeof voted_by === "undefined") {
        return;
      }
      if (this.player.id === voted_user) {
        this.isVoted = false;
      }
      this.gameStore.currentVote = 0;
      this.votedBy.splice(this.votedBy.indexOf(voted_by), 1)
      this.gameStore.unVote({
        userID: voted_user,
        votedBy: voted_by,
      });
      if (emit_event) {
        Echo.join(`game.${this.gameId}`).whisper("game.unvoting", {
          voted_user: voted_user,
          voted_by: voted_by,
        });
      }
    },
  },
};
</script>

<style scoped></style>
