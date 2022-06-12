<template>
  <img
    :src="'https://localhost' + message.author.avatar"
    alt=""
    class="message__avatar"
  >
  <div class="message__texts">
    <p class="message__author">
      {{ message.author.username }}
    </p>
    <p class="message__content">
      {{ message.content }}
    </p>
  </div>
</template>

<script>
export default {
	name: "ChatMessage",
	props: {
		message: Object
	}
};
</script>

<style scoped></style>
