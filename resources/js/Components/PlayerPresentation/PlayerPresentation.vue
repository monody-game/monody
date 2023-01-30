<template>
  <div class="player-presentation__container">
    <div class="player-presentation__main">
      <div class="pill pill-light player-presentation__exp">
        {{ store.exp }}/100
      </div>
      <ProgressBar style="position: relative;">
        <img
          :src="store.avatar"
          alt=""
          class="player-presentation__avatar"
        >
      </ProgressBar>
      <span class="pill pill-light player-presentation__level">{{ store.username }}</span>
      <div />
    </div>
  </div>
</template>

<script setup>
import { useStore } from "../../stores/user";
import ProgressBar from "./ExpProgressBar.vue";

const store = useStore();
</script>
