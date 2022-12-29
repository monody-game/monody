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
        <svg class="chat__submit-icon">
          <use
            ref="icon"
            href="/sprite.svg#send"
          />
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
const gameStore = useGameStore();
const userStore = useUserStore();
const route = useRoute();

const send = async function() {
	await ChatService.send(message.value);
	message.value = "";
};

window.Echo.join(`game.${route.params.id}`)
	.listen(".chat.send", (e) => {
		const payload = e.data.payload;
		ChatService.sendMessage(payload, payload.type);
	})
	.listen(".game.role-assign", async (role_id) => {
		const res = await window.JSONFetch(`/roles/get/${role_id}`, "GET");
		const role = res.data.role;
		gameStore.setRole(userStore.id, role);
		await ChatService.sendMessage({
			type: "info",
			content: `Votre rôle est : ${role.display_name}`
		});
	})
	.listen(".game.kill", async (e) => {
		const payload = e.data.payload;
		const killed = payload.killedUser;
		const context = payload.context;

		if (killed === null) {
			if (context === "vote") {
				ChatService.sendMessage({
					content: "Le village a décidé de ne tuer personne aujourd'hui !",
					type: "death"
				});
			} else {
				ChatService.sendMessage({ content: "Personne n'a été tué cette nuit !", type: "death" });
			}
			return;
		}

		const user = gameStore.getPlayerByID(killed);
		const role = await window.JSONFetch(`/game/user/${user.id}/role`, "GET");

		if (context === "vote") {
			ChatService.sendMessage(
				{
					content: `Le village a décidé de tuer ${user.username} qui était ${role.data.display_name}`,
					type: "death"
				}
			);
		} else {
			ChatService.sendMessage(
				{
					content: `${user.username} a été tué cette nuit, il était ${role.data.display_name} !`,
					type: "death"
				}
			);
		}
	})
	.listen(".chat.lock", () => {
		input.value.disabled = !input.value.disabled;
		input.value.classList.toggle("locked");
		button.value.classList.toggle("locked");

		if (input.value.disabled) {
			input.value.placeholder = "Chat verrouillé";
			icon.value.setAttribute("href", "/sprite.svg#lock");
		} else {
			input.value.placeholder = "Envoyer un message";
			icon.value.setAttribute("href", "/sprite.svg#send");
		}
	})
	.listen(".game.end", async (e) => {
		const data = e.data.payload;
		const winners = Object.keys(data.winners);
		const team = await window.JSONFetch(`/team/${data.winningTeam}`, "GET");

		await ChatService.sendMessage({
			type: "info",
			content: `La partie a été remportée par ${winners.map(user => gameStore.getPlayerByID(user).username).join(" ")} du camp des ${team.data.team.display_name}`
		});
	});
</script>
