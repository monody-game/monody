<template>
	<BaseModal>
		<header>
			<h3>{{ $t("stats.title") }}</h3>
		</header>
		<div class="modal__page">
			<UserStatistics></UserStatistics>
		</div>
		<div class="modal__buttons">
			<div class="modal__buttons-right">
				<button class="btn medium" @click="useStore().close()">
					{{ $t("modal.close") }}
				</button>
			</div>
		</div>
	</BaseModal>
</template>

<script setup>
import BaseModal from "./BaseModal.vue";
import UserStatistics from "../PlayerPresentation/UserStatistics.vue";
import { useStore } from "../../stores/modals/modal.js";
</script>
