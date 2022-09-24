<template>
  <div class="role-selector__container">
    <div class="role-selector__main">
      <button
        :class="count === 0 ? 'disable-hover' : ''"
        :disabled="count === 0"
        class="btn small"
        @click="substract()"
      >
        -
      </button>
      <img
        :alt="props.role.name"
        :src="props.role.image"
        :title="props.role.display_name"
        class="role-selector__image"
      >
      <button
        :class="count >= default_limit || count >= props.role.limit ? 'disable-hover' : ''"
        :disabled="count >= default_limit || count >= props.role.limit"
        class="btn small"
        @click="add()"
      >
        +
      </button>
    </div>
    <p class="role-selector__count">
      {{ count }}
    </p>
  </div>
</template>

<script setup>
import { useStore } from "../../../../stores/GameCreationModal.js";
import { ref } from "vue";

const props = defineProps({
	role: {
		type: Object,
		required: true
	}
});

const store = useStore();
const default_limit = ref(10);

const currentSelectedCount = function () {
	return store.getRoleCountById(props.role.id) ?? 0;
};

const count = ref(currentSelectedCount());

const substract = function () {
	if (count.value === 0) {
		return;
	}
	store.removeSelectedRole(props.role.id);
	count.value = count.value - 1;
};

const add = function() {
	const hasLimit = Object.keys(props.role).includes("limit");
	if (hasLimit && count.value >= props.role["limit"]) {
		return;
	} else if (!hasLimit && count.value >= default_limit.value) {
		return;
	}
	count.value = count.value + 1;
	store.selectedRoles.push(props.role.id);
};
</script>
