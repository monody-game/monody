<template>
	<div class="innout__message">
		<p v-if="props.join">
			<svg>
				<use href="/sprite.svg#enter" />
			</svg>
			<span class="bold">{{ props.username }}</span
			>&nbsp;a rejoint la partie !
		</p>
		<p v-else>
			<svg>
				<use href="/sprite.svg#leave" />
			</svg>
			<span class="bold">{{ props.username }}</span
			>&nbsp;a quitté la partie
		</p>
	</div>
</template>

<script setup>
const props = defineProps({
	join: {
		type: Boolean,
		required: true,
	},
	username: {
		type: String,
		required: true,
	},
});
</script>
