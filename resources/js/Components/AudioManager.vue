<template>
	<div
		@click="modalStore.open('audio-management')"
		class="audio-manager_wrapper"
	>
		<svg>
			<use href="/sprite.svg#vocal"></use>
		</svg>
	</div>
</template>

<script setup>
import { Howl, Howler } from "howler";
import { onBeforeRouteLeave, useRoute } from "vue-router";
import { useStore } from "../stores/modals/audio-modal.js";
import { useStore as useModalStore } from "../stores/modals/modal.js";
import { onMounted } from "vue";

const store = useStore();
const modalStore = useModalStore();
const route = useRoute();

onMounted(() => {
	const storage = JSON.parse(
		localStorage.getItem("volume") ?? JSON.stringify({ ambient: 5, music: 7 }),
	);

	store.volumes.ambient = storage.ambient;
	store.volumes.music = storage.music;
});

const rooster = new Howl({
	src: ["../sounds/rooster.webm", "../sounds/rooster.mp3"],
	volume: store.volumes.ambient * 0.1,
});

const day = new Howl({
	src: ["../sounds/day.webm", "../sounds/day.mp3"],
	volume: store.volumes.music * 0.1,
});

const night = new Howl({
	src: ["../sounds/night.webm", "../sounds/night.mp3"],
	volume: store.volumes.music * 0.1,
});

store.$subscribe((mutation, state) => {
	localStorage.setItem("volume", JSON.stringify(state.volumes));

	if (state.volumes.music === 0) {
		night.mute();
		day.mute();
		rooster.mute();
	}

	night.volume(state.volumes.music * 0.1);
	day.volume(state.volumes.music * 0.1);
	rooster.volume(state.volumes.ambient * 0.1);
});

window.Echo.join(`game.${route.params.id}`).listen(
	".game.state",
	async (data) => {
		switch (data.status) {
			case 6:
				night.fade(store.volumes.music, 0, 500);
				setTimeout(() => night.stop(), 2000);
				rooster.play();
				day.play();
				day.fade(0, store.volumes.music, 2000);
				break;
			case 2:
				day.fade(store.volumes.music, 0, 500);
				setTimeout(() => day.stop(), 2000);
				night.play();
				night.fade(0, store.volumes.music, 2000);
				break;
			case 8:
				night.fade(store.volumes.music, 0, 500);
				day.fade(store.volumes.music, 0, 500);
				setTimeout(() => night.stop(), 2000);
				setTimeout(() => day.stop(), 2000);
		}
	},
);

onBeforeRouteLeave(() => {
	Howler.stop();
});
</script>
