<template>
  <div
    class="message__main"
    :class="message.type === 'werewolf' ? 'message__werewolf' : ''"
  >
    <img
      :src="avatar"
      alt=""
      class="message__avatar"
    >
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
const props = defineProps({
	message: {
		type: Object,
		required: true
	}
});
const avatar = window.location.origin + props.message.author.avatar + "?h=50&dpr=2";
</script>
