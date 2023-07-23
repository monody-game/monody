<template>
	<svg class="lang-switcher__flag pointer" @click="switchLanguage">
		<use :href="`/sprite.svg#${currentLang}`" />
	</svg>
</template>

<script setup>
import { useI18n } from "vue-i18n";
import { ref } from "vue";
import { useCache } from "../composables/cache.js";

const i18n = useI18n({ useScope: "global" });
const cache = useCache();
const currentLang = ref(
	localStorage.hasOwnProperty("lang")
		? localStorage.getItem("lang")
		: navigator.language.split("-")[0]
);

function switchLanguage() {
	currentLang.value = currentLang.value === "fr" ? "en" : "fr";
	localStorage.setItem("lang", currentLang.value);
	i18n.locale.value = currentLang.value;
	cache.clear();
	location.reload();
}
</script>
