<template>
  <img :src="'http://localhost:8000' + message.author.avatar" alt="" class="message__avatar"/>
  <div class="message__texts">
    <p class="message__author">{{ message.author.username }}</p>
    <p class="message__content">{{ message.content }}</p>
  </div>
</template>

<script>
export default {
  name: "Message",
  props: ["message"]
};
</script>

<style scoped></style>
