import { defineStore } from "pinia";
import { nextTick, reactive, ref } from "vue";
import { useStore as useUserStore } from "./user.js";
import { useRoute } from "vue-router";

export const useStore = defineStore("chat", () => {
	const id = useRoute().params.id;
	let messages = reactive({ main: [], couple: [] });
	let unread = reactive({ main: false, couple: true });

	function $reset() {
		messages = { main: [], couple: [] };
		unread = { main: false, couple: true };
	}

	function send(content, type, author = null, actionList = []) {
		const stored = JSON.parse(localStorage.getItem("messages")) ?? {};

		if (!(id in stored)) {
			stored[id] = { main: [], couple: [] };
		}

		if (type === "couple") {
			this.unread.couple =
				author !== null ? author.id !== useUserStore.id : false;
			this.messages.couple.push({
				content,
				type: "message",
				author: author,
				actionList: actionList,
				timestamp: Date.now(),
			});

			stored[id].couple = [...stored[id].couple, this.messages.couple.at(-1)];
		} else {
			this.unread.main =
				author !== null ? author.id !== useUserStore.id : false;
			this.messages.main.push({
				content,
				type,
				author: author,
				actionList: actionList,
				timestamp: Date.now(),
			});

			stored[id].main = [...stored[id].main, this.messages.main.at(-1)];
		}

		localStorage.setItem("messages", JSON.stringify(stored));

		nextTick(() => {
			const messageContainer = document.querySelector(".chat__messages");
			if (!messageContainer) return;
			messageContainer.scrollTo(0, messageContainer.scrollHeight);
		});
	}

	return { messages, unread, send, $reset };
});
