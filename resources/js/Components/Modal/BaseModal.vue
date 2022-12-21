<template>
  <div
    ref="modal-background"
    class="modal__background"
    @click="closeModal()"
  >
    <div
      ref="modal"
      aria-modal="true"
      role="dialog"
      class="modal__main"
      tabindex="-1"
      aria-labelledby="modal__title"
      @click.stop=""
      @keyup.esc="closeModal()"
    >
      <slot />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useStore } from "../../stores/modal.js";

const store = useStore();
const modal = ref(null);

onMounted(() => {
	modal.value.focus();
});

const closeModal = function () {
	store.close()
};
</script>
