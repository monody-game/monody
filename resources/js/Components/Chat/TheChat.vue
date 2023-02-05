<template>
  <div class="chat__main">
    <div class="chat__messages">
      <template
        v-for="message in store.messages"
        :key="message.content + message.timestamp"
      >
        <TimeSeparator
          v-if="message.type === 'time_separator'"
          :message="message.content"
        />
        <ChatAlert
          v-else-if="message.type !== 'message' && message.type !== 'werewolf'"
          :message="message.content"
          :type="message.type"
        />
        <ChatMessage
          v-else
          :message="message"
        />
      </template>
    </div>
    <div class="chat__submit-form">
      <input
        ref="input"
        v-model="content"
        class="chat__send-input"
        placeholder="Envoyer un message"
        type="text"
        @keyup.enter="sendMessage()"
      >
      <button
        ref="button"
        aria-label="Envoyer"
        class="chat__send-button"
        type="submit"
        :class="content.length > 500 ? 'locked' : ''"
        @click.prevent="sendMessage()"
        @keyup.stop
      >
        <svg class="chat__submit-icon">
          <use
            ref="icon"
            :href="content.length > 500 ? '/sprite.svg#lock' : '/sprite.svg#send'"
          />
        </svg>
        <span v-if="content.length > 500">
          {{ content.length }}/500
        </span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRoute } from "vue-router";
import { useStore as useGameStore } from "../../stores/game.js";
import { useStore } from "../../stores/chat.js";
import { send } from "../../services/sendMessage.js";
import ChatAlert from "./ChatAlert.vue";
import ChatMessage from "./ChatMessage.vue";
import TimeSeparator from "./TimeSeparator.vue";

const content = ref("");
const input = ref(null);
const button = ref(null);
const icon = ref(null);
const gameStore = useGameStore();
const route = useRoute();
const store = useStore();

const sendMessage = async function() {
	if (content.value.length < 500) {
		await send(content.value);
	}
	content.value = "";
};

window.Echo.join(`game.${route.params.id}`)
	.listen(".chat.send", (e) => {
		const payload = e.data.payload;
		store.send(payload.content, payload.type, payload.author);
	})
	.listen(".game.kill", async (e) => {
		const payload = e.data.payload;
		const killed = payload.killedUser;
		const context = payload.context;

		if (killed === null) {
			if (context === "vote") {
				store.send("Le village a décidé de ne tuer personne aujourd'hui !", "death");
			} else {
				store.send("Personne n'a été tué cette nuit !", "death");
			}
			return;
		}

		const user = gameStore.getPlayerByID(killed);
		const role = await window.JSONFetch(`/game/user/${user.id}/role`, "GET");

		if (context === "vote") {
			store.send(`Le village a décidé de tuer ${user.username} qui était ${role.data.display_name}`, "death");
		} else {			store.send(`${user.username} a été tué cette nuit, il était ${role.data.display_name} !`, "death");
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
		store.send(`La partie a été remportée par ${winners.map(user => gameStore.getPlayerByID(user).username).join(" ")} du camp des ${team.data.team.display_name}`, "info");
	});
</script>
