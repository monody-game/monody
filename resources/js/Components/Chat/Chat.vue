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
import ChatService from "@/services/ChatService.js";

export default {
  name: "Chat",
  methods: {
    isNight: function () {
      return document.body.classList.contains("night") === true;
    },
    isReadonly: function () {
      return this.isNight() === true ? "chat__submit-readonly" : "";
    },
    async send() {
      await this.service.send(this.message);
      this.message = "";
    },
  },
  data() {
    return {
      message: "",
      service: new ChatService(),
    };
  },
  mounted() {
    Echo.join(`game.${this.$route.params.id}`)
      .listen('.chat.send', (e) => {
        const message = e.data.message
        this.service.sendMessage({content: message.content, author: message.author});
      })
      .listen('.game.role-assign', async (role_id) => {
        const res = await JSONFetch(`/roles/get/${role_id}`, 'GET')
        console.log(res.data)
      })
  },
};
</script>

<style scoped></style>
