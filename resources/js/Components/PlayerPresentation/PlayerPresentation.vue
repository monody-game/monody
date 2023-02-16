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
import confetti from "canvas-confetti";
import { useStore } from "../../stores/user.js";
import { useStore as useAlertStore } from "../../stores/alerts.js";
import { useStore as useModalStore } from "../../stores/modals/modal.js";
import { useStore as usePopupStore } from "../../stores/modals/popup.js";
import ProgressBar from "./ExpProgressBar.vue";
import UserStatistics from "./UserStatistics.vue";

const store = useStore();
const modalStore = useModalStore();
const popupStore = usePopupStore();
const alertStore = useAlertStore();

window.Echo.private("App.Models.User." + store.id)
	.notification((notification) => {
		switch (notification.data.type) {
		case "exp.earn":
			if (notification.data.amount > 0 && notification.data.amount - store.exp > 0) {
				alertStore.addAlerts({
					"level": `Vous venez de gagner ${notification.data.amount - store.exp} exp`
				});
			}

			store.exp = notification.data.amount;
			break;
		case "exp.levelup":
			store.exp_needed = notification.data.exp_needed;
			store.level = notification.data.level;
			popupStore.setPopup({
				level: {
					title: "Bravo !",
					content: `Vous venez de passer niveau ${store.level} ! Continuez ainsi !`,
					note: "Accumulez de l'expérience en jouant sur Monody !"
				}
			});

			if (window.matchMedia("(prefers-reduced-motion: reduce)") === false || window.matchMedia("(prefers-reduced-motion: reduce)").matches === false) {
				startParty();
			}
		}
	});

const soon = () => {
	alertStore.addAlerts({
		"info": "Un jour, peut-être ..."
	});
};

const startParty = () => {
	const duration = 2 * 1000;
	const animationEnd = Date.now() + duration;
	const defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 10000 };

	function randomInRange(min, max) {
		return Math.random() * (max - min) + min;
	}

	const interval = setInterval(function() {
		const timeLeft = animationEnd - Date.now();

		if (timeLeft <= 0) {
			return clearInterval(interval);
		}

		const particleCount = 50 * (timeLeft / duration);
		confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 } }));
		confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 } }));
	}, 250);
};
</script>
