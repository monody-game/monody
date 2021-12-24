<template>
  <div class="chat__main">
    <div class="chat__messages"></div>
    <div class="chat__submit-form">
      <input
        v-model="message"
        :class="isReadonly()"
        :readonly="isNight()"
        class="chat__send-input"
        placeholder="Envoyer un message"
        type="text"
        @keyup.enter="send()"
      />
      <button
        aria-label="Envoyer"
        class="chat__send-button"
        @click="send()"
        @keyup.stop
      >
        <svg class="chat__submit-icon">
          <use href="/sprite.svg#send"></use>
        </svg>
      </button>
    </div>
  </div>
</template>

<script>
import ChatService from "@/services/ChatService";

export default {
  name: "Chat",
  methods: {
    isNight: function () {
      return document.body.classList.contains("night") === true;
    },
    isReadonly: function () {
      return this.isNight() === true ? "chat__submit-readonly" : "";
    },
    async send () {
      await this.service.send(this.message, this.$store);
      this.message = "";
    },
  },
  data () {
    return {
      message: "",
      service: new ChatService(),
    };
  },
  mounted () {
    Echo.private(`game.${this.$route.params.id}`)
    .listen('MessageSended', (e) => {
      const message = e.message
      this.service.sendMessage({ content: message.content, author: message.author });
    })/*.listen("game.day", () => {
      this.message = "";
    }).listen("game.night", () => {
      this.message = "";
    }).listen("messages", ({ messages }) => {
      for (const k in messages) {
        this.service.sendMessage(messages[k]);
      }
    });*/
  },
};
</script>

<style scoped></style>
