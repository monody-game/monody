<template>
	<div class="countdown__wrapper">
		<svg>
			<use href="/sprite.svg#monody"></use>
		</svg>

		<h1>{{ ("0" + days).slice(-2) }}:{{ ("0" + hours).slice(-2) }}:{{ ("0" + minutes).slice(-2) }}:{{ ("0" + seconds).slice(-2) }}</h1>
	</div>
	<FooterComponent></FooterComponent>
</template>

<script setup>
import FooterComponent from "../Components/FooterComponent.vue";
import { ref } from "vue";
import confetti from "canvas-confetti";

// MM/DD/YYYY HH:MM
const end = new Date("08/24/2023 17:00")
let distance = end - Date.now()

const second = 1000
const minute = second * 60
const hour = minute * 60
const day = hour * 24

const seconds = ref(0)
const minutes = ref(0)
const hours = ref(0)
const days = ref(0)

const interval = setInterval(() => {
	if (end.getTime() < Date.now()) {
		clearInterval(interval)
		startParty()
		return;
	}

	distance = end - Date.now()

	seconds.value = Math.floor(distance % minute / second)
	minutes.value = Math.floor(distance % hour / minute)
	hours.value = Math.floor(distance % day / hour)
	days.value = Math.floor(distance / day)
}, 1000)

const startParty = () => {
	const duration = 10 * 1000;
	const animationEnd = Date.now() + duration;
	const defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 10000, disableForReducedMotion: false };

	function randomInRange(min, max) {
		return Math.random() * (max - min) + min;
	}

	const interval = setInterval(function () {
		const timeLeft = animationEnd - Date.now();

		if (timeLeft <= 0) {
			return clearInterval(interval);
		}

		const particleCount = 50 * (timeLeft / duration);
		confetti(
			Object.assign({}, defaults, {
				particleCount,
				origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 },
			}),
		);
		confetti(
			Object.assign({}, defaults, {
				particleCount,
				origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 },
			}),
		);
	}, 250);
};
</script>

<style scoped>
.footer__main {
	color: var(--light-background);
}

.countdown__wrapper {
	display: flex;
	justify-content: center;
	align-items: center;
	flex-direction: column;
	gap: 16px;

	height: calc(100vh - 32px);

	background-color: var(--dark-background);
	color: var(--light-background);
}

h1 {
	font-size: 6rem;
}

svg {
	height: 150px;
	aspect-ratio: 1;
}

@media screen and (max-width: 600px) {
  h1 {
    font-size: 3.25rem;
  }

  svg {
    height: 75px;
    aspect-ratio: 1;
  }
}
</style>
