<template>
	<div class="innout__message">
		<p v-if="props.join">
			<svg>
				<use href="/sprite.svg#enter" />
			</svg>
			<span class="bold">{{ props.username }}</span
			>&nbsp;{{ $t("chat.join") }}
		</p>
		<p v-else>
			<svg>
				<use href="/sprite.svg#leave" />
			</svg>
			<span class="bold">{{ props.username }}</span
			>&nbsp;{{ $t("chat.leave") }}
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
