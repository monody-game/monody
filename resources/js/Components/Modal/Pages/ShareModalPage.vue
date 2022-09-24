<template>
  <div>
    Partagez votre partie !
    <div class="share-page__container">
      <a
        :href="twitterLink"
        class="share-page__twitter"
        about="blank"
      >
        <svg>
          <use href="/sprite.svg#twitter" />
        </svg>
      </a>
    </div>
  </div>
</template>

<script setup>
import { useStore } from "../../../stores/GameCreationModal.js";
import { computed } from "vue";

const store = useStore();

const twitterLink = computed(() => {
	return "https://twitter.com/intent/tweet?text=Rejoignez%20moi%20dans%20ma%20partie%20Monody%F0%9F%8C%99%20%21%20" + encodeURIComponent(getLink);
});

const getLink = computed(() => {
	const id = store.gameId;
	if (id) {
		return "https://localhost/game/" + id;
	}
	return null;
});
</script>
