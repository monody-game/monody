<template>
	<div :class="'alert-message__' + props.type">
		<div class="alert-message__content">
			<svg class="alert-message__icon">
				<use :href="'/sprite.svg#' + props.type" />
			</svg>
			<p class="alert-message__text">
				{{ props.message }}
			</p>
		</div>
		<div v-if="props.actions.length >= 1" class="alert-message__actions">
			<div
				v-for="action in props.actions"
				:key="action.title"
				class="btn medium"
				@click="action.callback()"
			>
				{{ action.title }}
			</div>
		</div>
	</div>
</template>

<script setup>
const props = defineProps({
	message: {
		type: String,
		required: true,
	},
	type: {
		type: String,
		required: true,
	},
	actions: {
		type: Array,
		required: false,
		default: () => [],
	},
});
</script>
