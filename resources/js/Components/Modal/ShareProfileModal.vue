<template>
	<BaseModal>
		<header>
			<h3>Partagez votre profil</h3>
		</header>
		<div v-if="loading" class="auth-page__loading-group">
			<div class="auth-page__loading-group-blur" />
			<DotsSpinner />
		</div>
		<div
			class="share-profile__template"
			:class="
				theme === 'light'
					? 'share-profile__template-light'
					: 'share-profile__template-dark'
			"
		>
			<article class="share-profile__template-wrapper">
				<section>
					<img
						:src="store.avatar + '?w=100&dpr=2'"
						:alt="store.username + '\'s avatar'"
					/>
					<div>
						<h4>
							{{ store.username }}
						</h4>
						<div class="share-profile__template-stats">
							<div title="Niveau">
								<svg>
									<use href="/sprite.svg#level" />
								</svg>
								<p>Niveau : N/A</p>
							</div>
							<div title="Elo">
								<svg>
									<use href="/sprite.svg#elo" />
								</svg>
								<p>Elo : N/A</p>
							</div>
						</div>
						<div class="share-profile__template-progress-bar">
							<span />
							<p>N/A</p>
						</div>
					</div>
				</section>
				<footer>
					&copy; Monody {{ new Date().getFullYear() }} — Tous droits réservés
				</footer>
			</article>
		</div>
		<div class="share-profile__template-theme-switcher">
			<input
				class="theme-switcher__checkbox"
				type="checkbox"
				:checked="theme === 'dark'"
				@click="switchTheme"
			/>
			<svg id="sun">
				<use href="/sprite.svg#day" />
			</svg>
			<svg id="moon">
				<use href="/sprite.svg#night" />
			</svg>
		</div>
		<div class="share-profile__link-container">
			<a class="share-profile__link" href="#" @click="image">
				<svg>
					<use href="/sprite.svg#image" />
				</svg>
				Copier l'image
			</a>
			<a class="share-profile__link" href="#" @click="link()">
				<svg>
					<use href="/sprite.svg#chain" />
				</svg>
				Copier le lien
			</a>
		</div>
	</BaseModal>
</template>

<script setup>
import { ref } from "vue";
import { useStore } from "../../stores/user.js";
import { useStore as useAlertStore } from "../../stores/alerts.js";
import BaseModal from "./BaseModal.vue";
import DotsSpinner from "../Spinners/DotsSpinner.vue";

const store = useStore();
const alertStore = useAlertStore();
const theme = ref("dark");
const loading = ref(false);

if (
	window.matchMedia("(prefers-color-scheme: dark)") === false ||
	window.matchMedia("(prefers-color-scheme: dark)").matches === false
) {
	theme.value = "light";
}

const switchTheme = () => {
	if (theme.value === "dark") {
		theme.value = "light";
		return;
	}

	theme.value = "dark";
};

const link = async () => {
	await generate();

	await navigator.clipboard.writeText(
		`${location.origin}/assets/profiles/${store.id}.png`
	);
	alertStore.addAlerts({
		info: "Le lien vers le profil a été copié dans le presse-papiers",
	});
};

const image = async () => {
	await generate();
	const res = await fetch(`/assets/profiles/${store.id}.png`);
	const blob = await res.blob();

	if (typeof ClipboardItem === "undefined") {
		await navigator.clipboard.writeText(
			`${location.origin}/assets/profiles/${store.id}.png`
		);
		alertStore.addAlerts({
			warn: "Votre navigateur ne supporte pas cette fonctionnalité",
		});
		alertStore.addAlerts({
			info: "Le lien vers le profil a été copié dans le presse-papiers",
		});
		return;
	}

	await navigator.clipboard.write([
		new ClipboardItem({
			"image/png": blob,
		}),
	]);

	alertStore.addAlerts({
		info: "Le profil a été copié dans le presse-papiers",
	});
};

const generate = async () => {
	loading.value = true;
	await window.JSONFetch(`/user/share/${theme.value}`, "GET");
	loading.value = false;
};
</script>
