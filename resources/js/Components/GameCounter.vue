<template>
  <div class="counter__main">
    <span class="counter__icon-container">
      <svg
        class="counter__icon-circle"
        height="45"
        viewBox="0 0 45 45"
        width="45"
        xmlns="http://www.w3.org/2000/svg"
      >
        <circle
          cx="22.5"
          cy="22.5"
          fill="none"
          r="20"
          stroke="white"
        />
      </svg>
      <svg
        ref="counterIcon"
        class="counter__icon"
      >
        <use :href="'/sprite.svg#' + icon" />
      </svg>
    </span>
    <p class="counter__seconds">
      {{ new Date(time * 1000).toISOString().substr(14, 5) }}
    </p>
    <p class="counter__round">
      &nbsp;- {{ roundText }}
    </p>
  </div>
</template>

<script setup>
import ChatService from "../services/ChatService";
import { useStore } from "../stores/game";
import { onMounted, ref } from "vue";
import { onBeforeRouteLeave, useRoute } from "vue-router";

const route = useRoute();
const round = ref(0);
const icon = ref("");
const time = ref(0);
const startingTime = ref(0);
const totalTime = ref(0);
const counterId = ref(null);
const counterIcon = ref(null);
const status = ref(0);
const chatService = new ChatService();
const sound = new Audio("../sounds/bip.mp3");
const roundText = ref("");

const getState = async function(toRetrieveState = null) {
	const parameter = toRetrieveState === null ? status.value : toRetrieveState;
	const state = await window.JSONFetch(`/state/${parameter}`, "GET");
	return state.data;
};

onMounted(() =>	updateCircle());

let state = await getState();
sound.load();
roundText.value = state.name;
icon.value = state.icon;

window.Echo.join(`game.${route.params.id}`)
	.listen(".game.state", async (data) => {
		if (data) {
			clearInterval(counterId.value);
			time.value = data.counterDuration === -1 ? 0 : data.counterDuration;
			startingTime.value = data.startTimestamp;
			totalTime.value = time.value;
			status.value = data.state;
			round.value = data.round;
			state = await getState(data.state.value);
			roundText.value = state.name;
			icon.value = state.icon;
			useStore().state = data.state;
			updateCircle();
			decount();
			updateOverlay();
			updateBackground(state.background);
		}
	});

onBeforeRouteLeave(() => {
	clearInterval(counterId.value);
});

const decount = function () {
	if (time.value === 0) {
		return;
	}
	counterId.value = window.setInterval(() => {
		time.value = time.value - 1;
		soundManagement();
		updateCircle();

		if (time.value === 0) {
			clearInterval(counterId.value);
		}
	}, 1000);
};

const soundManagement = function () {
	switch (time.value) {
	case 120:
	case 60:
	case 30:
	case 10:
	case 5:
	case 3:
	case 2:
	case 1:
		sound.currentTime = 0;
		sound.play();
		break;
	}
};

const updateCircle = function () {
	const circle = document.querySelector(".counter__icon-circle circle");
	if (circle) {
		let percentage = (time.value / totalTime.value) * 100;

		if (totalTime.value === 0) {
			percentage = 100;
		}

		const circumference = Math.PI * 2 * 20;
		const offset = (circumference * percentage) / 100 - circumference;

		circle.style.strokeDasharray = `${circumference}, ${circumference}`;
		circle.style.strokeDashoffset = `${offset}`;
	}
};

const updateOverlay = function () {
	switch (status.value) {
	default:
		break;
	case 2:
		chatService.timeSeparator("Tombée de la nuit");
		counterIcon.value.classList.remove("counter__icon-rotate");
		break;
	case 6:
		chatService.timeSeparator("Lever du jour");
		counterIcon.value.classList.add("counter__icon-rotate");
		break;
	case 8:
		counterIcon.value.classList.remove("counter__icon-rotate");
		break;
	}
};

const updateBackground = function (background) {
	const gamePageWrapper = document.querySelector(".game-page__container");
	gamePageWrapper.classList.remove("day", "night");
	gamePageWrapper.classList.add(background);
};
</script>
