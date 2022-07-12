<template>
  <div class="chat__main">
    <div class="chat__messages" />
    <div class="chat__submit-form">
      <input
        v-model="message"
        :class="isReadonly()"
        :readonly="isNight()"
        class="chat__send-input"
        placeholder="Envoyer un message"
        type="text"
        @keyup.enter="send()"
      >
      <button
        aria-label="Envoyer"
        class="chat__send-button"
        @click="send()"
        @keyup.stop
      >
        <svg class="chat__submit-icon">
          <use href="/sprite.svg#send" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script>
import ChatService from "../../services/ChatService.js";
import { useStore as useGameStore } from "../../stores/game.js";
import { useStore as useUserStore } from "../../stores/user.js";

export default {
	name: "TheChat",
	data() {
		return {
			message: "",
			service: new ChatService(),
			gameStore: useGameStore(),
			userStore: useUserStore()
		};
	},
	mounted() {
		window.Echo.join(`game.${this.$route.params.id}`)
			.listen(".chat.send", (e) => {
				this.service.sendMessage(e.data.message, e.data.type ?? "message");
			})
			.listen(".game.role-assign", async (role_id) => {
				const res = await window.JSONFetch(`/roles/get/${role_id}`, "GET");
				const role = res.data.role;
				this.gameStore.setRole(this.userStore.id, role);
			})
			.listen(".chat.werewolf", (e) => {
				this.service.sendMessage({ content: e.content, author: e.author }, "message", "message__werewolf");
			})
			.listen(".game.kill", (e) => {
				const killed = e.data.payload.killedUser;

				if (killed === null) {
					this.service.sendMessage("Personne n'a été tué !", "death");
					return;
				}

				const user = this.gameStore.getPlayerByID(killed);
				this.service.sendMessage(`${user.username} a été tué !`, "death");
			});
	},
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
};
</script>
