<template>
	<div
		ref="badge"
		class="badge-presentation__wrapper"
		:data-owned="props.badge.owned"
	>
		<img
			v-if="props.badge.owned"
			:src="`/assets/badges/${store.theme}/${name}?w=60&dpr=2`"
			:alt="props.badge.display_name"
		/>
		<p v-else>?</p>
		<div v-if="props.badge.max_level > 0" class="badge-presentation__level">
			<span
				v-for="n in props.badge.max_level"
				:key="n"
				:data-filled="n <= props.badge.current_level"
			/>
		</div>
		<div
			v-if="props.badge.owned"
			ref="description"
			class="badge-presentation__description"
		>
			<h4>{{ props.badge.display_name }}</h4>
			<div
				v-if="props.badge.max_level > 0"
				class="badge-presentation__description-levels"
			>
				<template v-for="n in props.badge.max_level">
					<img
						v-if="n <= props.badge.current_level"
						:key="n"
						:src="`/assets/badges/${store.theme}/${props.badge.name}_${n}.png?w=60&dpr=2`"
						:alt="props.badge.display_name"
					/>
					<div v-else :key="-n">?</div>
				</template>
			</div>
			<p v-if="props.badge.description !== null">
				{{ props.badge.description }}
			</p>
		</div>
	</div>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import { useStore } from "../stores/user.js";

const store = useStore();
const description = ref(null);
const badge = ref(null);

const props = defineProps({
	badge: {
		type: Object,
		required: true,
	},
});

const name = computed(() => {
	if (props.badge.current_level > 0) {
		return `${props.badge.name}_${props.badge.current_level}.png`;
	}

	return `${props.badge.name}.png`;
});

onMounted(() => {
	if (props.badge.owned === true) {
		badge.value.addEventListener("pointerenter", () => {
			description.value.style.display = "grid";
		});

		badge.value.addEventListener("pointerleave", () => {
			description.value.style.display = "none";
		});
	}
});
</script>
