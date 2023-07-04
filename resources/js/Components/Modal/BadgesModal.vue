<template>
	<BaseModal wrapper="badge-modal__main">
		<header>
			<h3>Badges</h3>
		</header>
		<div class="badges-modal__page">
			<BadgePresentation
				v-for="badge in badges"
				:key="badge.id"
				:badge="badge"
			/>
		</div>
		<div class="modal__buttons">
			<div class="modal__buttons-right">
				<button class="btn medium" @click="close">Fermer</button>
			</div>
		</div>
	</BaseModal>
</template>

<script setup>
import BaseModal from "./BaseModal.vue";
import { useStore } from "../../stores/modals/badges.js";
import { useStore as useModalStore } from "../../stores/modals/modal.js";
import BadgePresentation from "../BadgePresentation.vue";
import { computed } from "vue";

const store = useStore();
const modalStore = useModalStore();

const badges = computed(() => {
	return [...store.badges]
		.sort((a, b) => b.owned - a.owned)
		.filter((badge) => !(badge.owned === false && badge.secret === true));
});

if (store.badges.length === 0) {
	const res = await window.JSONFetch("/badges");
	store.badges = res.data.badges;
}

const close = () => {
	modalStore.close();
};
</script>
