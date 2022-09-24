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
        type="submit"
        @click.prevent="send()"
        @keyup.stop
      >
        <svg class="chat__submit-icon">
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
const service = new ChatService();
const gameStore = useGameStore();
const userStore = useUserStore();
const route = useRoute();

const isNight = function () {
	return document.body.classList.contains("night") === true;
};

const isReadonly = function () {
	return isNight() === true ? "chat__submit-readonly" : "";
};

const send = async function() {
	await service.send(message.value);
	message.value = "";
};

window.Echo.join(`game.${route.params.id}`)
	.listen(".chat.send", (e) => {
		service.sendMessage(e.data.message, e.data.type ?? "message");
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
			if (context === "werewolf") {
				service.sendMessage("Personne n'a été tué cette nuit !", "death");
			} else if (context === "vote") {
				service.sendMessage("Le village a décidé de ne tuer personne aujourd'hui !", "death");
			}
			return;
		}

		const user = gameStore.getPlayerByID(killed);
		const role = await window.JSONFetch(`/game/user/${user.id}/role`, "GET");

		if (context === "werewolf") {
			service.sendMessage(
				`${user.username} a été tué cette nuit, il était ${role.data.display_name} !`,
				"death"
			);
		} else if (context === "vote") {
			service.sendMessage(
				`Le village a décidé de tuer ${user.username} qui était ${role.data.display_name}`,
				"death"
			);
		}
	});
</script>
