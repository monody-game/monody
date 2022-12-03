import ChatMessage from "../Components/Chat/Message.vue";
import ChatAlert from "../Components/Chat/ChatAlert.vue";
import TimeSeparator from "../Components/Chat/TimeSeparator.vue";
import { createApp } from "vue";

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

		await window.JSONFetch("/game/message/send", "POST", {
			content: message,
			gameId
		});
	}
}
