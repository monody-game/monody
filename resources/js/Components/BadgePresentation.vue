<template>
  <div
    class="badge-presentation__wrapper"
    :data-owned="props.badge.owned"
  >
    <img
      v-if="props.badge.owned"
      :src="`/assets/badges/${store.theme}/${name}`"
      :alt="props.badge.display_name"
    >
    <p v-else>
      ?
    </p>
    <div
      v-if="props.badge.max_level > 0"
      class="badge-presentation__level"
    >
      <span
        v-for="n in props.badge.max_level"
        :key="n"
        :data-filled="n <= props.badge.current_level"
      />
    </div>
  </div>
</template>

<script setup>
import { useStore } from "../stores/user.js";
import { computed } from "vue";

const store = useStore();

const props = defineProps({
	badge: {
		type: Object,
		required: true
	}
});

const name = computed(() => {
	if (props.badge.current_level > 0) {
		return `${props.badge.name}_${props.badge.current_level}.png`;
	}

	return `${props.badge.name}.png`;
});
</script>
