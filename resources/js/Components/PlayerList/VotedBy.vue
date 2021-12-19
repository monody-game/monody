<template>
  <div class="voted-by__container">
    <img
      v-for="voter in votedBy"
      :key="voter"
      :alt="getPlayerByID(voter).username + `'s avatar`"
      :src="getAvatar(voter)"
      class="voted-by__avatar"/>
  </div>
</template>
<script>
export default {
  name: "VotedBy",
  props: ["player", "votedBy"],
  methods: {
    getPlayerByID (id) {
      return this.$store.getters.getPlayerByID(id);
    },
    getAvatar (id) {
      return (
        "http://localhost:8000" + this.getPlayerByID(id).avatar
      );
    },
  },
};
</script>
