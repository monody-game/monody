// noinspection ES6UnusedImports
import Vue from "vue";
import App from "./App.vue";
import router from "./router/Router";
import Store from "./store/BaseStore";

console.log("%cAttention !", "color: #273097; font-size: 64px; font-weight: bold");
console.log("%cSi quelqu'un vous a demandé de coller quelque chose ici, il y a à peu près 100 chances sur 10 que ça soit une arnaque.", "font-size: 20px");
console.log("%cFermez cette fenêtre sauf si vous savez exactement ce que vous faites.", "color: red; font-size: 20px");

window.addEventListener("devtoolschange", event => {
  if (event.detail.isOpen) {
    console.log("%cAttention !", "color: #273097; font-size: 64px; font-weight: bold");
    console.log("%cSi quelqu'un vous a demandé de coller quelque chose ici, il y a à peu près 100 chances sur 10 que ça soit une arnaque.", "font-size: 20px");
    console.log("%cFermez cette fenêtre sauf si vous savez exactement ce que vous faites.", "color: red; font-size: 20px");
  }
});

require("./Helpers.js");

new Vue({
  router,
  el: "#app",
  store: Store,
  render: (h) => h(App),
});

Vue.config.productionTip = false;
