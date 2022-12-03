<template>
  <div class="chat__main">
    <div class="chat__messages" />
    <div class="chat__submit-form">
      <input
        ref="input"
        v-model="message"
        class="chat__send-input"
        placeholder="Envoyer un message"
        type="text"
        @keyup.enter="send()"
      >
      <button
        ref="button"
        aria-label="Envoyer"
        class="chat__send-button"
        type="submit"
        @click.prevent="send()"
        @keyup.stop
      >
        <svg
          ref="icon"
          class="chat__submit-icon"
        >
          <use href="/sprite.svg#send" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import ChatService from "../../services/ChatService.js";
import { useStore as useGameStore } from "../../stores/game.js";
import { useStore as useUserStore } from "../../stores/user.js";
import { ref } from "vue";
import { useRoute } from "vue-router";

const message = ref("");
const input = ref(null);
const button = ref(null);
const icon = ref(null);
const service = new ChatService();
const gameStore = useGameStore();
const userStore = useUserStore();
const route = useRoute();

const send = async function() {
	await service.send(message.value);
	message.value = "";
};

window.Echo.join(`game.${route.params.id}`)
	.listen(".chat.send", (e) => {
		console.log(e);
		service.sendMessage(e.data.payload, e.data.type ?? "message");
	})
	.listen(".game.role-assign", async (role_id) => {
		const res = await window.JSONFetch(`/roles/get/${role_id}`, "GET");
		const role = res.data.role;
		gameStore.setRole(userStore.id, role);
	})
	.listen(".chat.werewolf", (e) => {
		service.sendMessage({ content: e.content, author: e.author }, "message", "message__werewolf");
	})
	.listen(".game.kill", async (e) => {
		const killed = e.data.payload.killedUser;
		const context = e.data.payload.context;

		if (killed === null) {
			if (context === "vote") {
				service.sendMessage("Le village a décidé de ne tuer personne aujourd'hui !", "death");
			} else {
				service.sendMessage("Personne n'a été tué cette nuit !", "death");
			}
			return;
		}

		const user = gameStore.getPlayerByID(killed);
		const role = await window.JSONFetch(`/game/user/${user.id}/role`, "GET");

		if (context === "vote") {
			service.sendMessage(
				`Le village a décidé de tuer ${user.username} qui était ${role.data.display_name}`,
				"death"
			);
		} else {
			service.sendMessage(
				`${user.username} a été tué cette nuit, il était ${role.data.display_name} !`,
				"death"
			);
		}
	})
	.listen(".chat.lock", () => {
		input.value.disabled = !input.value.disabled;
		input.value.classList.toggle("locked");
		button.value.classList.toggle("locked");

		if (input.value.disabled) {
			console.log("locking");
			input.value.placeholder = "Chat verrouillé";
			icon.value.setAttribute("href", "/sprite.svg#lock");
		} else {
			console.log("unlocking");
			input.value.placeholder = "Envoyer un message";
			icon.value.setAttribute("href", "/sprite.svg#send");
		}
	});
</script>
