<template>
	<BaseModal wrapper="popup__wrapper" data-popup-type="warn">
		<header class="popup__header">
			<div class="popup__header-left">
				<svg class="popup__icon">
					<use href="/sprite.svg#warn" />
				</svg>
				<p id="modal__title" class="popup__title bold">
					{{ $t("popup.warn") }}
				</p>
			</div>
			<svg
				class="popup__close"
				tabindex="0"
				@keydown.enter="no()"
				@keydown.space="no()"
				@click="no()"
			>
				<use href="/sprite.svg#cross" />
			</svg>
		</header>
		<p class="popup__content">{{ $t("profile.avatar_warn_content") }}</p>
		<div class="modal__buttons" style="">
			<button class="btn medium" @click="no()">
				{{ $t("modal.cancel") }}
			</button>
			<button class="btn medium" @click="yes()">
				{{ $t("modal.confirm") }}
			</button>
		</div>
	</BaseModal>
</template>

<script setup>
import BaseModal from "./BaseModal.vue";
import { useAvatarWarnPopupStore } from "../../stores/modals/avatar-warn-popup.js";
import { useStore as useModalStore } from "../../stores/modals/modal.js";
import { useStore as useAlertStore } from "../../stores/alerts.js";
import { useStore as useUserStore } from "../../stores/user.js";
import { useCache } from "../../composables/cache.js";
import { useI18n } from "vue-i18n";

const { t } = useI18n();
const popupStore = useAvatarWarnPopupStore();
const modalStore = useModalStore();
const alertStore = useAlertStore();
const userStore = useUserStore();

const yes = async () => {
	popupStore.close();

	const res = await window.JSONFetch("/avatars", "DELETE");

	if (res.ok) {
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

		alertStore.addAlerts({
			success: t("profile.avatar_delete_success"),
		});
	}
};

const no = () => {
	popupStore.close();
};
</script>
