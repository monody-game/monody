import { defineStore } from "pinia";
import { nextTick, reactive, ref } from "vue";
import { useStore as useUserStore } from "./user.js";
import { useRoute } from "vue-router";

export const useStore = defineStore("chat", () => {
	const id = ref(useRoute().params.id);
	const messages = ref({ main: [], couple: [] });
	const unread = ref({ main: false, couple: true });

	function $reset() {
		id.value = useRoute().params.id;
		messages.value = { main: [], couple: [] };
		unread.value = { main: false, couple: true };
	}

	function send(content, type, author = null, actionList = []) {
		const stored = JSON.parse(localStorage.getItem("messages")) ?? {};

		if (!(id.value in stored)) {
			stored[id.value] = { main: [], couple: [] };
		}

		if (type === "couple") {
			this.unread.couple =
				author !== null ? author.id !== useUserStore().id : false;
			this.messages.couple.push({
				content,
				type: "message",
				author: author,
				actionList: actionList,
				timestamp: Date.now(),
			});

			stored[id.value].couple = [
				...stored[id.value].couple,
				this.messages.couple.at(-1),
			];
		} else {
			this.unread.main =
				author !== null ? author.id !== useUserStore().id : false;
			this.messages.main.push({
				content,
				type,
				author: author,
				actionList: actionList,
				timestamp: Date.now(),
			});

			stored[id.value].main = [
				...stored[id.value].main,
				this.messages.main.at(-1),
			];
		}

		localStorage.setItem("messages", JSON.stringify(stored));

		nextTick(() => {
			const messageContainer = document.querySelector(".chat__messages");
			if (!messageContainer) return;
			messageContainer.scrollTo(0, messageContainer.scrollHeight);
		});
	}

	return { id, messages, unread, send, $reset };
});
