<template>
  <BaseModal>
    <header>
      <h3>Êtes-vous encore là ?</h3>
    </header>

    <div class="share-page__container">
      <div
        class="progress-bar"
        :style="`background-image: conic-gradient(from 0deg at 50% 50%, var(--accent-primary) 0 ${progress}%, var(--primary) 0);`"
      >
        <p style="font-size: 28px;">
          {{ time }}
        </p>
      </div>
    </div>

    <div class="modal__buttons">
      <button
        class="btn medium"
        style="width: 200px;"
        @click="no()"
      >
        Non
      </button>
      <button
        class="btn medium"
        style="width: 200px;"
        @click="yes()"
      >
        Oui
      </button>
    </div>
  </BaseModal>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import BaseModal from "./BaseModal.vue";
import { useStore as useModalStore } from "../../stores/modals/modal.js";

const modalStore = useModalStore();
const router = useRouter();
const progress = ref(100);
const time = ref(30);

const interval = setInterval(() => {
	time.value--;
	progress.value = time.value * 100 / 30;

	if (time.value === 0) {
		no();
	}
}, 1000);

const yes = () => {
	modalStore.close();
	clearInterval(interval);
};

const no = () => {
	modalStore.close();
	clearInterval(interval);

	router.push("play");
};
</script>
