<template>
	<BaseModal class="share-page__background">
		<header class="share-page__header">
			<h3>{{ $t("share_game.title") }}</h3>
			<svg
				class="popup__close"
				tabindex="0"
				@click="store.close()"
				@keydown.enter="store.close()"
				@keydown.space="store.close()"
			>
				<use href="/sprite.svg#cross" />
			</svg>
		</header>
		<div class="share-page__wrapper">
			<div class="share-page__container">
				<a
					target="_blank"
					:href="twitterLink"
					class="share-page__social-twitter"
				>
					<svg>
						<use href="/sprite.svg#twitter" />
					</svg>
					{{ $t("share_game.twitter") }}
				</a>
				<a class="share-page__social-discord" href="#" @click.prevent="share()">
					<svg>
						<use href="/sprite.svg#discord" />
					</svg>
					{{ $t("share_game.discord") }}
				</a>
				<a class="share-page__social-link" href="#" @click.prevent="copyLink()">
					<svg>
						<use href="/sprite.svg#chain" />
					</svg>
					{{ $t("share_game.link") }}
				</a>
			</div>
		</div>
	</BaseModal>
</template>

<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";
import { useStore as useAlertStore } from "../../stores/alerts.js";
import { useStore } from "../../stores/modals/modal.js";
import BaseModal from "./BaseModal.vue";
import { useI18n } from "vue-i18n";

const route = useRoute();
const store = useStore();
const alertStore = useAlertStore();
const { t } = useI18n();

const gameId = route.params.id;
const link = window.location.origin + "/game/" + gameId;

const copyLink = () => {
	navigator.clipboard.writeText(link);
	alertStore.addAlerts({ info: t("share_game.copied") });
};

const share = async () => {
	const res = await window.JSONFetch("/game/share");

	if (res.ok) {
		alertStore.addAlerts({
			success: t("share_game.shared"),
		});
	} else {
		alertStore.addAlerts({
			warn: t("share_game.share_error"),
		});
	}

	store.close();
};

const twitterLink = computed(() => {
	return (
		"https://twitter.com/intent/tweet?text=" +
		t("share_game.twitter_content") +
		encodeURIComponent(link)
	);
});
</script>
