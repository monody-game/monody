<template>
	<div
		class="message__main"
		:class="message.type === 'werewolf' ? 'message__werewolf' : ''"
	>
		<div class="message__avatar">
			<span v-if="props.message.type === 'dead'" class="message__avatar-dead">
				<span />
				<svg>
					<use href="/sprite.svg#death" />
				</svg>
			</span>
			<span v-if="isWerewolf === true" class="message__is-wolf" />
			<img :src="avatar" alt="" />
		</div>
		<div class="message__texts">
			<p class="message__author">
				{{ props.message.author.username }}
			</p>
			<p class="message__content">
				{{ props.message.content }}
			</p>
		</div>
	</div>
</template>

<script setup>
import { useStore } from "../../stores/game.js";
import { ref } from "vue";

const props = defineProps({
	message: {
		type: Object,
		required: true,
	},
});

const store = useStore();
const avatar = props.message.author.avatar + "?h=50&dpr=2";

const isWerewolf = ref(store.werewolves.includes(props.message.author.id));

store.$subscribe((mutation, state) => {
	if (state.werewolves.includes(props.message.author.id)) {
		isWerewolf.value = true;
	}
});
</script>
