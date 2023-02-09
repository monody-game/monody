import { defineStore } from "pinia";
import { nextTick } from "vue";

export const useStore = defineStore("chat", {
	state: () => {
		return {
			messages: []
		};
	},
	actions: {
		send(content, type, author = null, actionList = []) {
			this.messages.push({
				content,
				type,
				author: author,
				actionList: actionList,
				timestamp: Date.now()
			});

			nextTick(() => {
				const messageContainer = document.querySelector(".chat__messages");
				messageContainer.scrollTo(0, messageContainer.scrollHeight);
			});
		}
	}
});
