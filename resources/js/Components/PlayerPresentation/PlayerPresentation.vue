<template>
  <div class="player-presentation__container">
    <div class="player-presentation__main">
      <div class="pill pill-light player-presentation__exp">
        {{ store.exp }}/100
      </div>
      <ProgressBar style="position: relative;">
        <img
          :src="store.avatar + '?h=200&dpr=2'"
          alt=""
          class="player-presentation__avatar"
        >
        <div class="player-presentation__levels">
          <div>
            <svg>
              <use href="/sprite.svg#level" />
            </svg>
            <p>{{ store.level }}</p>
          </div>
          <div>
            <svg>
              <use href="/sprite.svg#elo" />
            </svg>
            <p>N/A</p>
          </div>
        </div>
      </ProgressBar>
      <span class="pill pill-light player-presentation__level">{{ store.username }}</span>
      <UserStatistics />
      <div class="player-presentation__footer">
        <svg @click="soon()">
          <use href="/sprite.svg#wheel" />
        </svg>
        <svg @click="soon()">
          <use href="/sprite.svg#share" />
        </svg>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useStore } from "../../stores/user";
import { useStore as useAlertStore } from "../../stores/alerts";
import ProgressBar from "./ExpProgressBar.vue";
import UserStatistics from "./UserStatistics.vue";

const store = useStore();
const soon = () => {
	useAlertStore().addAlerts({
		"info": "Un jour, peut-être ..."
	});
};
</script>
