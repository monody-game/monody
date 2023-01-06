<template>
  <div
    class="debug-bar__wrapper"
    :data-openned="barOpenned"
    :title="barOpenned ? 'Fermer la barre de débug' : 'Ouvrir la barre de débug'"
    @click.prevent="switchState"
  >
    <div
      v-if="barOpenned"
      class="debug-bar__content"
    >
      az
    </div>
    <svg>
      <use href="/sprite.svg#beta" />
    </svg>
  </div>
</template>

<script setup>
import { ref } from "vue";

const barOpenned = ref(false);

const switchState = function () {
	barOpenned.value = !barOpenned.value;
};
</script>
