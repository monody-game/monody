import Vuex from "vuex";
import Vue from "vue";
import UserStore from "@/store/UserStore.js";
import ModalStore from "@/store/ModalStore.js";
import GameStore from "@/store/GameStore.js";

const isStrict = true;

Vue.use(Vuex);

const Store = new Vuex.Store({
  strict: isStrict,
  modules: {
    UserStore,
    ModalStore,
    GameStore,
  },
});

export default Store;
