<template>
	<div class="player-presentation__container">
		<div class="player-presentation__main">
			<svg
				class="player-presentation__leaderboards"
				@click="modalStore.open('leaderboards')"
			>
				<use href="/sprite.svg#ranking" />
			</svg>
			<svg
				class="player-presentation__badges"
				@click="modalStore.open('badges')"
			>
				<use :href="'/sprite.svg#badges_' + store.theme" />
			</svg>
			<div class="pill pill-light player-presentation__exp">
				{{ store.exp }}/{{ store.exp_needed }}
			</div>
			<ProgressBar style="position: relative">
				<img
					:src="store.avatar + '?h=200&dpr=2'"
					alt=""
					class="player-presentation__avatar"
				/>
			</ProgressBar>
			<div class="player-presentation__levels">
				<div title="Niveau">
					<svg>
						<use href="/sprite.svg#level" />
					</svg>
					<p>Niveau {{ store.level }}</p>
				</div>
			</div>
			<span class="pill pill-light player-presentation__name">{{
				store.username
			}}</span>
			<button class="btn large" @click="modalStore.open('statistics-modal')">
				{{ $t("stats.button") }}
			</button>
			<div class="player-presentation__footer">
				<svg @click="modalStore.open('profile-modal')">
					<use href="/sprite.svg#wheel" />
				</svg>
				<svg @click="modalStore.open('share-profile')">
					<use href="/sprite.svg#share" />
				</svg>
			</div>
		</div>
	</div>
</template>

<script setup>
import { useStore } from "../../stores/user.js";
import { useStore as useModalStore } from "../../stores/modals/modal.js";
import ProgressBar from "./ExpProgressBar.vue";
import UserStatistics from "./UserStatistics.vue";

const store = useStore();
const modalStore = useModalStore();
</script>
