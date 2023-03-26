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
import { onMounted, ref } from "vue";
import { onBeforeRouteLeave, useRoute } from "vue-router";
import { useStore } from "../stores/game.js";
import { useStore as useChatStore } from "../stores/chat.js";
import { useStore as useModalStore } from "../stores/modals/modal.js";

const route = useRoute();
const round = ref(0);
const icon = ref("");
const time = ref(0);
const totalTime = ref(0);
const counterId = ref(0);
const counterIcon = ref(null);
const status = ref(0);
const sound = new Audio("../sounds/bip.mp3");
const roundText = ref("");
const chatStore = useChatStore();
const modalStore = useModalStore();
const halt = ref(false);

const getState = async function(toRetrieveState = null) {
	const parameter = toRetrieveState === null ? status.value : toRetrieveState;
	const state = await window.JSONFetch(`/state/${parameter}`, "GET");
	return state.data.state;
};

onMounted(() =>	updateCircle());

let state = await getState();
sound.load();
roundText.value = state.name;
icon.value = state.icon;

window.Echo.join(`game.${route.params.id}`)
	.listen(".game.state", async (data) => {
		if (data) {
			await setData(data);

			if (data.status !== 1) return;

			if (modalStore.opennedModal === "share-game-modal") {
				modalStore.close();
			}
		}
	})
	.listen(".game.data", async ({ data }) => {
		await setData(data.payload.state);
	});

onBeforeRouteLeave(() => {
	clearInterval(counterId.value);
	halt.value = true;
});

const setData = async function (data) {
	clearInterval(counterId.value);
	time.value = data.counterDuration === -1 ? 0 : getDuration(data.counterDuration, data.startTimestamp);
	totalTime.value = data.counterDuration === -1 ? 0 : data.counterDuration;

	if (status.value !== data.status.value) {
		status.value = data.status;
		state = await getState(data.status.value);
	}

	status.value = data.status;
	round.value = data.round;
	roundText.value = state.name;
	icon.value = state.icon;
	useStore().state = data.state;
	updateCircle();
	decount();
	updateOverlay();
	updateBackground(state.background);
};

const getDuration = function (duration, startTimestamp) {
	if (typeof startTimestamp === "undefined") {
		return duration;
	}

	const timestampDifference = (Date.now() - startTimestamp) / 1000;
	const difference = duration - timestampDifference.toFixed();

	if (difference <= 0) {
		return 0;
	}

	return difference;
};

const decount = function () {
	if (time.value <= 0) {
		clearInterval(counterId.value);
		return;
	}

	counterId.value = window.setInterval(() => {
		if (halt.value === true) {
			clearInterval(counterId.value);
			return;
		}

		time.value = time.value - 1;
		soundManagement();
		updateCircle();

		if (time.value <= 0) {
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
		chatStore.send("Tombée de la nuit", "time_separator");
		counterIcon.value.classList.remove("counter__icon-rotate");
		break;
	case 6:
		chatStore.send("Lever du jour", "time_separator");
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
