<template>
  <div
    class="role-selector__container"
    :data-has-operations="showOperations"
  >
    <div class="role-selector__main">
      <button
        v-if="showOperations"
        :class="count === 0 ? 'disable-hover' : ''"
        :disabled="count === 0"
        class="btn role-selector__operation-button role-selector__operation-minus"
        @click="substract()"
      >
        <svg>
          <use href="/sprite.svg#minus" />
        </svg>
      </button>
      <span class="role-selector__name">{{ props.role.display_name }}</span>
      <img
        :alt="props.role.name"
        :src="props.role.image + '?h=60&dpr=2'"
        :title="props.role.display_name"
        class="role-selector__image"
        :class="props.presentable ? 'pointer' : ''"
        @click="present()"
      >
      <button
        v-if="showOperations"
        :class="count >= default_limit || count >= props.role.limit ? 'disable-hover' : ''"
        :disabled="count >= default_limit || count >= props.role.limit"
        class="btn role-selector__operation-button role-selector__operation-add"
        @click="add()"
      >
        <svg>
          <use href="/sprite.svg#plus" />
        </svg>
      </button>
    </div>
    <div>
      <button
        v-if="!showOperations && count < 1 && !('id' in route.params)"
        class="btn role-selector__operation-button role-selector__count"
        @click="add()"
      >
        <svg>
          <use href="/sprite.svg#plus" />
        </svg>
      </button>
      <button
        v-else-if="!showOperations && count >= 1 && !('id' in route.params)"
        class="btn role-selector__operation-button role-selector__count"
        @click="substract()"
      >
        <svg>
          <use href="/sprite.svg#minus" />
        </svg>
      </button>
      <p
        v-else
        class="role-selector__count"
      >
        {{ count }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from "vue";
import { useRoute } from "vue-router";
import { useStore } from "../../../../stores/modals/game-creation-modal.js";

const props = defineProps({
	role: {
		type: Object,
		required: true
	},
	operations: {
		type: Boolean,
		required: false,
		default: true
	},
	presentable: {
		type: Boolean,
		default: false
	}
});

const emit = defineEmits(["role"]);

const store = useStore();
const route = useRoute();
const default_limit = ref(10);

const present = () => {
	if (!props.presentable) return;

	emit("role", props.role);
};

const showOperations = computed(() => {
	if ("limit" in props.role) {
		return props.operations && props.role.limit > 1;
	}
	return props.operations;
});

const currentSelectedCount = function () {
	return store.getRoleCountById(props.role.id) ?? 0;
};

const count = ref(props.role.count ?? currentSelectedCount());

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
