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
  props: ["socket"],
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
      service: new ChatService(this.socket)
    };
  },
  created () {
    (async () => {
      this.socket.on("chat.new", (message) => {
        const input = document.querySelector(".chat__send-input");
        this.service.sendMessage({ content: message.content, author: message.author });
        input.value = "";
      });

      this.socket.on("game.day", () => {
        this.message = "";
      });

      this.socket.on("game.night", () => {
        this.message = "";
      });

      this.socket.on("messages", ({ messages }) => {
        for (const k in messages) {
          this.service.sendMessage(messages[k]);
        }
      });
    })();
  },
};
</script>

<style scoped></style>
