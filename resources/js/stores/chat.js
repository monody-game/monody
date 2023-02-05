import { defineStore } from "pinia";

export const useStore = defineStore("chat", {
	state: () => {
		return {
			messages: []
		};
	},
	actions: {
		send(content, type, author = null, actionList = null) {
			this.messages.push({
				content,
				type,
				author,
				actionList
			});
		}
	}
});
