import { defineStore } from "pinia";
import { nextTick } from "vue";
import { useStore as useUserStore } from "./user.js";

export const useStore = defineStore("chat", {
	state: () => {
		return {
			messages: {
				main: [],
				couple: []
			},
			unread: {
				main: false,
				couple: true
			}
		};
	},
	actions: {
		send(content, type, author = null, actionList = []) {
			if (type === "couple") {
				this.unread.couple = author !== null ? author.id !== useUserStore.id : false;
				this.messages.couple.push({
					content,
					type: "message",
					author: author,
					actionList: actionList,
					timestamp: Date.now()
				});
			} else {
				this.unread.main = author !== null ? author.id !== useUserStore.id : false;
				this.messages.main.push({
					content,
					type,
					author: author,
					actionList: actionList,
					timestamp: Date.now()
				});
			}

			nextTick(() => {
				const messageContainer = document.querySelector(".chat__messages");
				if (!messageContainer) return;
				messageContainer.scrollTo(0, messageContainer.scrollHeight);
			});
		}
	}
});
