<template>
  <Suspense>
    <router-view />
  </Suspense>
  <AlertList />
</template>

<script setup>
import AlertList from "./Components/Alerts/AlertList.vue";
</script>
