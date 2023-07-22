<template>
	<div class="chat__main">
		<div v-if="gameStore.couple.includes(userStore.id)" class="chat__selector">
			<div
				:data-selected="chatSelected === 'main'"
				:data-unread="store.unread.main"
				@click="
					chatSelected = 'main';
					store.unread.main = false;
					messagesContainer.scrollTo(0, messagesContainer.scrollHeight);
				"
			>
				Main
			</div>
			<span class="chat__selector-separator" />
			<div
				:data-selected="chatSelected === 'couple'"
				:data-unread="store.unread.couple"
				@click="
					chatSelected = 'couple';
					store.unread.couple = false;
					messagesContainer.scrollTo(0, messagesContainer.scrollHeight);
				"
			>
				Couple
			</div>
		</div>
		<div class="chat__messages" ref="messagesContainer">
			<template
				v-for="message in store.messages[chatSelected]"
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
					v-else-if="
						message.type === 'message' ||
						message.type === 'couple' ||
						message.type === 'werewolf' ||
						message.type === 'dead'
					"
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
				:disabled="isLocked"
				:class="{ locked: isLocked }"
				class="chat__send-input"
				:placeholder="isLocked ? $t('chat.locked') : $t('chat.send')"
				type="text"
				@keyup.enter="sendMessage()"
			/>
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
						:href="
							content.length > 500 || isLocked
								? '/sprite.svg#lock'
								: '/sprite.svg#send'
						"
					/>
				</svg>
				<span v-if="content.length > 500"> {{ content.length }}/500 </span>
			</button>
		</div>
	</div>
</template>

<script setup>
import { ref } from "vue";
import { onBeforeRouteLeave, useRoute } from "vue-router";
import { useStore as useGameStore } from "../../stores/game.js";
import { useStore as useUserStore } from "../../stores/user.js";
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
const isLocked = ref(false);
const gameStore = useGameStore();
const userStore = useUserStore();
const route = useRoute();
const store = useStore();
let interval = null;
const { t } = ref(null);

const chatSelected = ref("main");
const messagesContainer = ref(null);

const sendMessage = async function () {
	if (content.value.length < 500) {
		await send(content.value, chatSelected.value);
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
		store.send(payload.content, payload.type, payload.author, []);
	})
	.listen(".game.kill", async (e) => {
		const payload = e.data.payload;
		const killed = payload.killedUser;
		const context = payload.context;

		if (killed === null) {
			if (context === "vote") {
				store.send(t("chat.no_voted"), "death");
			} else {
				store.send(t("chat.no_killed_night"), "death");
			}
			return;
		}

		const user = gameStore.getPlayerByID(killed);
		let role = await window.JSONFetch(
			`/game/${route.params.id}/user/${user.id}/role`,
			"GET"
		);
		role = role.data.role.display_name;

		if (payload.infected && payload.infected === true) {
			role = role + ` (${t("chat.infected")})`;
		}

		if (context === "vote") {
			store.send(t("chat.vote", { user: user.username, role }), "death");
		} else if (context === "bitten") {
			store.send(t("chat.bitten", { user: user.username, role }), "death");
		} else if (context === "couple") {
			store.send(t("chat.couple", { user: user.username, role }), "death");
		} else if (context === "hunter") {
			store.send(t("chat.hunter", { user: user.username, role }), "death");
		} else if (context === "disconnect") {
			store.send(t("chat.disconnect", { user: user.username, role }), "death");
		} else {
			store.send(t("chat.death", { user: user.username, role }), "death");
		}
	})
	.listen(".chat.lock", ({ data }) => {
		isLocked.value = data.payload.lock;
	})
	.listen(".game.end", async () => {
		interval = setTimeout(() => {
			useModalStore().open("activity-confirmation-modal");
		}, 60000);
	});
</script>
