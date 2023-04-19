<template>
  <div class="chat__main">
    <div class="chat__messages">
      <template
        v-for="message in store.messages"
        :key="message.content + message.timestamp"
      >
        <InAndOutMessage
          v-if="message.type === 'inandout_alert'"
          :username="message.author"
          :join="message.content"
        />
        <TimeSeparator
          v-else-if="message.type === 'time_separator'"
          :message="message.content"
        />
        <ChatMessage
          v-else-if="message.type === 'message' || message.type === 'werewolf' || message.type === 'dead'"
          :message="message"
        />
        <ChatAlert
          v-else
          :message="message.content"
          :type="message.type"
          :actions="message.actionList"
        />
      </template>
    </div>
    <div class="chat__submit-form">
      <input
        ref="input"
        v-model="content"
        :disabled="isLocked === true"
        :class="{locked: isLocked}"
        class="chat__send-input"
        :placeholder="isLocked ? 'Chat verrouillé' : 'Envoyer un message' "
        type="text"
        @keyup.enter="sendMessage()"
      >
      <button
        ref="button"
        aria-label="Envoyer"
        class="chat__send-button"
        type="submit"
        :class="content.length > 500 || isLocked ? 'locked' : ''"
        @click.prevent="sendMessage()"
        @keyup.stop
      >
        <svg class="chat__submit-icon">
          <use
            ref="icon"
            :href="content.length > 500 || isLocked ? '/sprite.svg#lock' : '/sprite.svg#send'"
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
import { onBeforeRouteLeave, useRoute } from "vue-router";
import { useStore as useGameStore } from "../../stores/game.js";
import { useStore as useModalStore } from "../../stores/modals/modal.js";
import { useStore } from "../../stores/chat.js";
import { send } from "../../services/sendMessage.js";
import ChatAlert from "./ChatAlert.vue";
import ChatMessage from "./ChatMessage.vue";
import TimeSeparator from "./TimeSeparator.vue";
import InAndOutMessage from "./InAndOutMessage.vue";

const content = ref("");
const input = ref(null);
const button = ref(null);
const icon = ref(null);
const gameStore = useGameStore();
const route = useRoute();
const store = useStore();
let interval = null;
const isLocked = ref(false);

gameStore.$subscribe((mutation, state) => {
	isLocked.value = state.chat_locked;
});

const sendMessage = async function() {
	if (content.value.length < 500) {
		await send(content.value);
	}
	content.value = "";
};

onBeforeRouteLeave(() => {
	store.$reset();

	if (interval !== null) {
		clearInterval(interval);
	}
});

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
		let role = await window.JSONFetch(`/game/user/${user.id}/role`, "GET");
		role = role.data.role.display_name;

		if (payload.infected && payload.infected === true) {
			role = role + " (infecté)";
		}

		if (context === "vote") {
			store.send(`Le village a décidé de tuer ${user.username} qui était ${role}`, "death");
		} else if (context === "bitten") {
			store.send(`${user.username} a succombé à ses blessures. Il était ${role}`, "death");
		} else if (context === "couple") {
			store.send(`Dans un élan de chagrin amoureux, ${user.username} rejoint son âme-soeur dans sa tombe, il était ${role}.`, "death");
		} else {
			store.send(`${user.username} a été tué cette nuit, il était ${role} !`, "death");
		}
	})
	.listen(".chat.lock", ({ data }) => {
		isLocked.value = data.payload.lock;
	})
	.listen(".game.end", async (e) => {
		const data = e.data.payload;
		const winners = Object.keys(data.winners);
		const team = await window.JSONFetch(`/team/${data.winningTeam}`, "GET");
		let message = `La partie a été remportée par ${winners.map(user => gameStore.getPlayerByID(user).username).join(", ")}`;

		if (team.data.team.name !== "loners") {
			message += ` du camp des ${team.data.team.display_name}`;
		} else {
			message += ` qui était ${Object.values(data.winners)[0].display_name}`;
		}

		store.send(message, "info");

		interval = setTimeout(() => {
			useModalStore().open("activity-confirmation-modal");
		}, 60000);
	});
</script>
