<template>
  <div>
    Partagez votre partie !
    <div class="share-page__container">
      <a
        :href="twitterLink"
        class="share-page__twitter"
      >
        <svg>
          <use href="/sprite.svg#twitter" />
        </svg>
      </a>
    </div>
  </div>
</template>

<script>
import { useStore } from "../../../stores/GameCreationModal.js";

export default {
	name: "ShareModalPage",
	data() {
		return {
			store: useStore()
		};
	},
	computed: {
		twitterLink() {
			return "https://twitter.com/intent/tweet?text=Rejoignez%20moi%20dans%20ma%20partie%20Monody%F0%9F%8C%99%20%21%20" + encodeURIComponent(this.getLink);
		},
		getLink() {
			const id = this.store.gameId;
			if (id) {
				return "https://localhost/game/" + id;
			}
		}
	},
};
</script>
