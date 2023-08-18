import { defineStore } from "pinia";

export const useStore = defineStore("logout-warn-popup", {
	state: () => {
		return {
			isOpenned: false,
		};
	},
	actions: {
		close() {
			this.isOpenned = false;
		},
	},
});
