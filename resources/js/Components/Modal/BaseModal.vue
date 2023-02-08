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
      :class="props.wrapper"
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
import { useStore } from "../../stores/modals/modal.js";

const props = defineProps({
	wrapper: {
		type: String,
		default: "modal__main"
	}
});

const store = useStore();
const modal = ref(null);

onMounted(() => {
	modal.value.focus();
});

const closeModal = function () {
	store.close();
};
</script>
