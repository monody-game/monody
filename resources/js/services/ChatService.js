import ChatMessage from "../Components/Chat/Message.vue";
import ChatAlert from "../Components/Chat/ChatAlert.vue";
import TimeSeparator from "../Components/Chat/TimeSeparator.vue";
import { createApp } from "vue";
import { useStore as useGameStore } from "../stores/game.js";
import { useStore as useUserStore } from "../stores/user.js";

export default class ChatService {
	timeSeparator(message) {
		const messageContainer = document.querySelector(".chat__messages");
		const wrapper = document.createElement("div");
		wrapper.classList.add("time-separator__main");

		createApp(TimeSeparator, {
			message: message,
		}).mount(wrapper);

		messageContainer.appendChild(wrapper);
		messageContainer.scrollTo(0, messageContainer.scrollHeight);
	}

	sendMessage(message, type, customClass) {
		const messageContainer = document.querySelector(".chat__messages");
		const wrapper = document.createElement("div");

		if (customClass) {
			wrapper.classList.add(customClass);
		}

		if (type !== "message") {
			wrapper.classList.add("alert-message__" + type);
			createApp(ChatAlert, {
				type: type,
				message: message
			})
				.use(window.pinia)
				.mount(wrapper);
		} else {
			wrapper.classList.add("message__main");
			createApp(ChatMessage, {
				message: message,
			}).mount(wrapper);
		}

		messageContainer.appendChild(wrapper);
		messageContainer.scrollTo(0, messageContainer.scrollHeight);
	}

	async send(message) {
		if (message === "") return;

		const gameId = window.location.pathname.split("/")[2];

		if (useGameStore().state === "GAME_WEREWOLF" && useGameStore().isWerewolf) {
			window.Echo.join(`game.${gameId}`)
				.whisper("chat.werewolf.send", { content: message, author: useUserStore().id });
			this.sendMessage({
				content: message,
				author: useUserStore().getUser
			}, "message__werewolf");
			return;
		}
		await window.JSONFetch("/game/message/send", "POST", {
			content: message,
			gameId
		});
	}

	lock() {
		const input = document.querySelector(".chat__send-input");
		const button = document.querySelector(".chat__send-button");
		const icon = document.querySelector(".chat__submit-icon use");

		icon.setAttribute("href", "/sprite.svg#lock");

		input.placeholder = "Chat verrouillé";
		input.disabled = true;
		input.classList.add("locked");
		button.classList.add("locked");
	}

	unlock() {
		const input = document.querySelector(".chat__send-input");
		const button = document.querySelector(".chat__send-button");
		const icon = document.querySelector(".chat__submit-icon use");

		icon.setAttribute("href", "/sprite.svg#send");

		input.placeholder = "Envoyer un message";
		input.disabled = false;
		input.classList.remove("locked");
		button.classList.remove("locked");
	}
}
