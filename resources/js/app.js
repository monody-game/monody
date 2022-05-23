import { createApp } from "vue";
import App from "./App.vue";
import router from "./router/Router";
import { createPinia } from "pinia";

require('./bootstrap.js');

window.pinia = createPinia()

createApp(App)
  .use(window.pinia)
  .use(router)
  .mount("#app");

require("./Helpers.js");
