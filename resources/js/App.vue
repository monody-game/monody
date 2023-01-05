<template>
  <DebugBar />
  <Transition name="modal">
    <PopupComponent v-if="popupStore.isOpenned" />
  </Transition>
  <Suspense>
    <router-view />
  </Suspense>
  <AlertList />
</template>

<script setup>
import AlertList from "./Components/Alerts/AlertList.vue";
import PopupComponent from "./Components/Alerts/PopupComponent.vue";
import { useStore } from "./stores/popup.js";
import DebugBar from "./Components/DebugBar.vue";

const popupStore = useStore();
</script>
