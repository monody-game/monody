<template>
  <div
    class="player__container player__votable"
    @click="vote(userID, player.id)"
    :data-id="player.id"
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

export default {
  name: "Player",
  props: ["player"],
  components: { VotedBy },
  data () {
    return {
      isVoted: this.player.voted_by.length > 1,
    };
  },
  computed: {
    userID () {
      return this.$store.getters.getUserId;
    },
    votedBy () {
      return this.player.voted_by;
    },
    getAvatar () {
      return "http://localhost:8000" + this.player.avatar;
    }
  },
  mounted () {
    /*this.socket.on("game.vote", ({ voted_user, voted_by }) => {
      if (voted_user === this.player.id) {
        this.vote(voted_by, voted_user, false);
      }
    });
    this.socket.on("game.unvote", ({ voted_user, voted_by }) => {
      if (voted_user === this.player.id) {
        this.unVote(voted_by, voted_user, false);
      }
    });*/
  },
  methods: {
    vote (voted_by, voted_user, emit_event = true) {
      if (typeof voted_by === "undefined") {
        return;
      }

      if (this.votedBy.includes(voted_by)) {
        this.unVote(voted_by, voted_user);
        return;
      }

      if (
        this.$store.getters.getCurrentVote > 0
      ) {
        this.unVote(voted_by, this.$store.getters.getCurrentVote);
        return;
      }

      this.isVoted = true;

      this.$store.commit("setVote", {
        userID: voted_user,
        votedBy: voted_by,
      });

      if (emit_event) {
        this.socket.emit("game.voting", {
          voted_user: voted_user,
          voted_by: voted_by,
        });
      }
    },
    unVote (voted_by, voted_user, emit_event = true) {
      if (typeof voted_by === "undefined") {
        return;
      }
      if (this.player.id === voted_user) {
        this.isVoted = false;
      }
      this.$store.commit("resetCurrentVote");
      this.$store.commit("unVote", {
        userID: voted_user,
        votedBy: voted_by,
      });
      if (emit_event) {
        this.socket.emit("game.unvoting", {
          voted_user: voted_user,
          voted_by: voted_by,
        });
      }
    },
  },
};
</script>

<style scoped></style>
