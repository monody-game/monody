import { createApp } from "vue";
import App from "./App.vue";
import router from "./router/Router.js";
import { createPinia } from "pinia";
import { createI18n } from "vue-i18n";
import "./bootstrap.js";
import "../scss/style.scss";
import SpinningDots from "@grafikart/spinning-dots-element";
import fr from "./locales/fr.json";
import en from "./locales/en.json";

customElements.define("spinning-dots", SpinningDots);

window.pinia = createPinia();

const currentLocale = localStorage.hasOwnProperty("lang")
	? localStorage.getItem("lang")
	: navigator.language.split("-")[0];

document.documentElement.lang = currentLocale;

const i18n = createI18n({
	locale: currentLocale,
	fallbackLocale: "fr",
	globalInjection: true,
	legacy: false,
	messages: {
		en,
		fr,
	},
});

createApp(App).use(window.pinia).use(router).use(i18n).mount("#app");

import "./Helpers.js";
import { useStore } from "./stores/debug-bar.js";

const store = useStore();

function proxy(context, method, message) {
	return function () {
		store[method.name + "s"].push(
			Object.values(arguments).filter((value) => value !== "\n")
		);
		method.apply(
			context,
			[message].concat(Array.prototype.slice.apply(arguments))
		);
	};
}

window.addEventListener("error", (e) => {
	store.errors.push({
		message: e.error.toString(),
		source: {
			file: e.filename,
			col: e.colno,
			line: e.lineno,
		},
	});
});

// let's do the actual proxying over originals
console.error = proxy(console, console.error, "Error:");
console.warn = proxy(console, console.warn, "Warning:");
