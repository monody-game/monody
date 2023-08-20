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
		<p class="popup__content">{{ $t("profile.logout_warn_content") }}</p>
		<div class="modal__buttons">
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
import { useStore as usePopupStore } from "../../stores/modals/logout-warn-popup.js";
import { useStore as useModalStore } from "../../stores/modals/modal.js";
import router from "../../router/Router.js";

const popupStore = usePopupStore();
const modalStore = useModalStore();

const yes = async () => {
	popupStore.close();

	await window.JSONFetch("/auth/logout/all", "POST");
	await router.push("/");
};

const no = () => {
	popupStore.close();
	modalStore.opennedModal = "profile-modal";
};
</script>
