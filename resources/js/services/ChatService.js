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

	sendMessage(payload, customClass) {
		const messageContainer = document.querySelector(".chat__messages");
		const wrapper = document.createElement("div");
		const type = payload.type;

		if (customClass) {
			wrapper.classList.add(customClass);
		}

		if (type !== "message" && type !== "werewolf") {
			wrapper.classList.add("alert-message__" + type);
			createApp(ChatAlert, {
				type: type,
				message: payload.content
			})
				.use(window.pinia)
				.mount(wrapper);
		} else {
			wrapper.classList.add("message__main");

			if (type === "werewolf") {
				wrapper.classList.add("message__werewolf");
			}

			createApp(ChatMessage, {
				message: payload,
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
