import { createApp } from "vue";
import App from "./App.vue";
import router from "./router/Router";
import { createPinia } from "pinia";

require('./bootstrap.js');

createApp(App)
  .use(createPinia())
  .use(router)
  .mount("#app");

require("./Helpers.js");
