<template>
  <div class="player-presentation__container">
    <div class="player-presentation__main">
      <div class="pill pill-light player-presentation__exp">
        {{ store.exp }}/{{ store.exp_needed }}
      </div>
      <ProgressBar style="position: relative;">
        <img
          :src="store.avatar + '?h=200&dpr=2'"
          alt=""
          class="player-presentation__avatar"
        >
        <div class="player-presentation__levels">
          <div
            title="Niveau"
          >
            <svg>
              <use href="/sprite.svg#level" />
            </svg>
            <p>{{ store.level }}</p>
          </div>
          <div
            title="Elo"
          >
            <svg>
              <use href="/sprite.svg#elo" />
            </svg>
            <p>N/A</p>
          </div>
        </div>
      </ProgressBar>
      <span class="pill pill-light player-presentation__name">{{ store.username }}</span>
      <UserStatistics />
      <div class="player-presentation__footer">
        <svg @click="modalStore.open('profile-modal')">
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
import { useStore } from "../../stores/user.js";
import { useStore as useAlertStore } from "../../stores/alerts.js";
import { useStore as useModalStore } from "../../stores/modals/modal.js";
import ProgressBar from "./ExpProgressBar.vue";
import UserStatistics from "./UserStatistics.vue";

const store = useStore();
const modalStore = useModalStore();

window.Echo.private("App.Models.User." + store.id)
	.notification((notification) => {
		console.log(notification);
		switch (notification.data.type) {
		case "exp.earn":
			store.exp = notification.data.amount;
			break;
		case "exp.levelup":
			store.exp_needed = notification.data.exp_needed;
			store.level = notification.data.level;
		}
	});

const soon = () => {
	useAlertStore().addAlerts({
		"info": "Un jour, peut-être ..."
	});
};
</script>
