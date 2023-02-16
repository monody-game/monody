import { defineStore } from "pinia";

export const useStore = defineStore("popup", {
	state: () => {
		return {
			isOpenned: false,
			type: "error",
			title: "",
			content: "",
			note: "",
			link: "",
			link_text: ""
		};
	},
	actions: {
		close() {
			this.isOpenned = false;
		},
		setPopup(payload) {
			for (const type in payload) {
				this.type = type;
				this.title = payload[type].title;
				this.content = payload[type].content;
				this.note = payload[type].note;
				this.link = payload[type].link;
				this.link_text = payload[type].link_text;
			}
			this.isOpenned = true;
		}
	}
});
