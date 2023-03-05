<template>
  <BaseModal class="share-page__background">
    <header class="share-page__header">
      <h3>Partagez votre partie !</h3>
      <svg
        class="popup__close"
        @click="store.close()"
      >
        <use href="/sprite.svg#cross" />
      </svg>
    </header>
    <div class="share-page__wrapper">
      <div class="share-page__container">
        <a
          target="_blank"
          :href="twitterLink"
          class="share-page__social-twitter"
        >
          <svg>
            <use href="/sprite.svg#twitter" />
          </svg>
          Partager sur Twitter
        </a>
        <a
          class="share-page__social-discord"
          href="#"
          @click.prevent="soon()"
        >
          <svg>
            <use href="/sprite.svg#discord" />
          </svg>
          Partager sur Discord
        </a>
        <a
          class="share-page__social-link"
          href="#"
          @click.prevent="copyLink()"
        >
          <svg>
            <use href="/sprite.svg#chain" />
          </svg>
          Copier le lien
        </a>
      </div>
    </div>
  </BaseModal>
</template>

<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";
import { useStore as useAlertStore } from "../../stores/alerts.js";
import { useStore } from "../../stores/modals/modal.js";
import BaseModal from "./BaseModal.vue";

const route = useRoute();
const store = useStore();
const alertStore = useAlertStore();

const gameId = route.params.id;
const link = window.location.origin + "/game/" + gameId;

const copyLink = () => {
	navigator.clipboard.writeText(link);
	alertStore.addAlerts({ "info": "Le lien a été copié dans le presse-papiers" });
};

const soon = () => {
	alertStore.addAlerts({ "info": "Un jour, peut-être ..." });
};

const twitterLink = computed(() => {
	return "https://twitter.com/intent/tweet?text=Rejoignez%20moi%20dans%20ma%20partie%20Monody%F0%9F%8C%99%20%21%20" + encodeURIComponent(link);
});
</script>
