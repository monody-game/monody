<template>
	<DebugBar v-if="isDev" />
	<Transition name="modal">
		<PopupComponent v-if="popupStore.isOpenned" />
	</Transition>
	<Suspense>
		<router-view />
	</Suspense>
	<AlertList />
</template>

<script setup>
import { ref, watch } from "vue";
import { useStore } from "./stores/modals/popup.js";
import { useStore as useUserStore } from "./stores/user.js";
import { useStore as useAlertStore } from "./stores/alerts.js";
import { useStore as useBadgesStore } from "./stores/modals/badges";
import DebugBar from "./Components/DebugBar.vue";
import AlertList from "./Components/Alerts/AlertList.vue";
import PopupComponent from "./Components/Alerts/PopupComponent.vue";
import confetti from "canvas-confetti";
import { useCache } from "./composables/cache.js";

const popupStore = useStore();
const userStore = useUserStore();
const alertStore = useAlertStore();
const badgeStore = useBadgesStore();
const url = new URL(window.location);
const isDev = ref(localStorage.getItem("dev") === "true");

const theme = ref(localStorage.getItem("theme") ?? "system");

if (theme.value === "system") {
	if (
		window.matchMedia("(prefers-color-scheme: dark)") === false ||
		window.matchMedia("(prefers-color-scheme: dark)").matches === false
	) {
		theme.value = "light";
	} else {
		theme.value = "dark";
	}
}

userStore.theme = theme.value;

document.documentElement.classList.remove(
	theme.value === "light" ? "dark" : "light",
);
document.documentElement.classList.add(
	theme.value === "light" ? "light" : "dark",
);

if (
	url.searchParams.has("pwa") &&
	localStorage.getItem("pwa_thanked") !== "true"
) {
	localStorage.setItem("pwa_thanked", true);
	alertStore.addAlerts({
		success: "Merci d'avoir installé Monody !",
	});
}

if (url.searchParams.has("token")) {
	localStorage.setItem(
		"restricted_request_token",
		url.searchParams.get("token"),
	);

	url.searchParams.delete("token");
	location.replace(url.href);
}

if (url.searchParams.has("cache") || url.searchParams.has("clearCache")) {
	useCache().clear();

	url.searchParams.delete("cache");
	url.searchParams.delete("clearCache");
	location.replace(url.href);
}

if (url.searchParams.has("flush")) {
	useCache().flush(url.searchParams.get("flush"));

	url.searchParams.delete("flush");
	location.replace(url.href);
}

if (url.searchParams.has("dev") || url.searchParams.has("debug")) {
	if (localStorage.getItem("dev") === "true") {
		localStorage.setItem("dev", false);
	} else {
		localStorage.setItem("dev", true);
	}

	url.searchParams.delete("dev");
	url.searchParams.delete("debug");
	location.replace(url.href);
}

const colorSchemeMedia = window.matchMedia("(prefers-color-scheme: dark)");

colorSchemeMedia.addEventListener("change", () => {
	if (colorSchemeMedia === false || colorSchemeMedia.matches === false) {
		userStore.theme = "light";
		document.documentElement.classList.remove("dark");
		document.documentElement.classList.add("light");
		return;
	}

	userStore.theme = "dark";
	document.documentElement.classList.remove("light");
	document.documentElement.classList.add("dark");
});

watch(userStore, () => subscribeToChannel());

const subscribeToChannel = () => {
	if (
		Object.hasOwn(
			window.Echo.connector.channels,
			`private-App.Models.User.${userStore.id}`,
		)
	)
		return;

	window.Echo.private("App.Models.User." + userStore.id)
		.notification((notification) => {
			switch (notification.data.type) {
				case "exp.earn":
					if (
						notification.data.amount > 0 &&
						notification.data.amount - userStore.exp > 0
					) {
						alertStore.addAlerts({
							level: `Vous venez de gagner ${
								notification.data.amount - userStore.exp
							} exp`,
						});
					}

					userStore.exp = notification.data.amount;
					break;
				case "exp.levelup":
					userStore.exp_needed = notification.data.exp_needed;
					userStore.level = notification.data.level;
					popupStore.setPopup({
						level: {
							title: "Bravo !",
							content: `Vous venez de passer niveau ${userStore.level} ! Continuez ainsi !`,
							note: "Accumulez de l'expérience en jouant sur Monody !",
						},
					});

					if (
						window.matchMedia("(prefers-reduced-motion: reduce)") === false ||
						window.matchMedia("(prefers-reduced-motion: reduce)").matches ===
							false
					) {
						startParty();
					}
					break;
				case "badge.granted":
					badgeStore.badges = [];

					popupStore.setPopup({
						level: {
							title: "Bravo !",
							content: `Vous venez de faire passer le badge ${notification.data.badge.display_name} au niveau ${notification.data.level} !`,
							note: "Il existe des badges cachés sur Monody !",
						},
					});

					if (
						window.matchMedia("(prefers-reduced-motion: reduce)") === false ||
						window.matchMedia("(prefers-reduced-motion: reduce)").matches ===
							false
					) {
						startParty();
					}
			}
		})
		.listen(".subscription_error", async () => {
			useCache().flush("/user");
			const res = await JSONFetch("/user");
			const user = res.data.user;

			userStore.setUser({
				id: user.id,
				username: user.username,
				email: user.email,
				email_verified_at: user.email_verified_at,
				avatar: user.avatar,
				level: user.level,
				exp: user.exp,
				exp_needed: user.next_level,
				discord_linked_at: user.discord_linked_at,
			});

			location.reload();
		});
};

const startParty = () => {
	const duration = 2 * 1000;
	const animationEnd = Date.now() + duration;
	const defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 10000 };

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
