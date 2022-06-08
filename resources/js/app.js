import { createApp } from "vue";
import App from "./App.vue";
import router from "./router/Router.js";
import { createPinia } from "pinia";
import "./bootstrap.js";
import "../scss/style.scss";

window.pinia = createPinia();

createApp(App)
	.use(window.pinia)
	.use(router)
	.mount("#app");

import "./Helpers.js";
