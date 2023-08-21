<template>
	<BaseModal wrapper="popup__wrapper" data-popup-type="info">
		<header class="popup__header">
			<div class="popup__header-left">
				<svg class="popup__icon">
					<use href="/sprite.svg#info" />
				</svg>
				<p id="modal__title" class="popup__title">{{ $t("modal.activity") }}</p>
			</div>
			<svg
				class="popup__close"
				tabindex="0"
				@keydown.enter="yes()"
				@keydown.space="yes()"
				@click="yes()"
			>
				<use href="/sprite.svg#cross" />
			</svg>
		</header>

		<div class="modal__buttons popup__content">
			<button class="btn medium" @click="no()">
				{{ $t("modal.leave") }}
			</button>
			<button class="btn medium" @click="yes()">
				{{ $t("modal.yes") }}
			</button>
		</div>
	</BaseModal>
</template>

<script setup>
import { useRouter } from "vue-router";
import BaseModal from "./BaseModal.vue";
import { useStore as useModalStore } from "../../stores/modals/modal.js";

const modalStore = useModalStore();
const router = useRouter();

const interval = setInterval(() => {
	no();
}, 30000);

const yes = () => {
	modalStore.close();
	clearInterval(interval);
};

const no = () => {
	modalStore.close();
	clearInterval(interval);

	router.push({ name: "play" });
};
</script>
