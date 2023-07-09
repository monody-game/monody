<template>
	<BaseModal>
		<div class="audio-manager__content">
			<h3>Gestion de l'audio</h3>
			<div class="audio-manager__sliders">
				<div class="audio-manager__slider-group">
					<label for="manager__ambient"
						>Volume des sons ({{
							(ambient * 0.1).toString().slice(0, 3)
						}}/1)</label
					>
					<div class="audio-manager__side-group">
						<svg
							class="pointer"
							@click="ambient = ambient === 0 ? storage.ambient : 0"
						>
							<use
								:href="'/sprite.svg#' + (ambient === 0 ? 'muted' : 'vocal')"
							></use>
						</svg>
						<input
							class="audio-manager__range"
							type="range"
							step="1"
							max="10"
							min="0"
							id="manager__ambient"
							v-model="ambient"
						/>
					</div>
				</div>
				<div class="audio-manager__slider-group">
					<label for="manager__music"
						>Volume de la musique ({{
							(music * 0.1).toString().slice(0, 3)
						}}/1)</label
					>
					<div class="audio-manager__side-group">
						<svg
							class="pointer"
							@click="music = music === 0 ? storage.music : 0"
						>
							<use
								:href="'/sprite.svg#' + (music === 0 ? 'muted' : 'vocal')"
							></use>
						</svg>
						<input
							class="audio-manager__range"
							type="range"
							step="1"
							max="10"
							min="0"
							id="manager__music"
							v-model="music"
						/>
					</div>
				</div>

				<button class="btn large" @click="save()">Valider</button>
			</div>
		</div>
	</BaseModal>
</template>

<script setup>
import BaseModal from "./BaseModal.vue";
import { ref } from "vue";
import { useStore } from "../../stores/modals/audio-modal.js";
import { useStore as useModalStore } from "../../stores/modals/modal.js";

const store = useStore();
const modalStore = useModalStore();

const storage = JSON.parse(
	localStorage.getItem("volume") ?? "{ ambient: 5, music: 7 }"
);

const music = ref(storage.music);
const ambient = ref(storage.ambient);

const save = () => {
	store.volumes.music = music.value;
	store.volumes.ambient = ambient.value;

	modalStore.close();
};
</script>
