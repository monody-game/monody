import { defineStore } from "pinia";

export const useStore = defineStore("share-game-modal", {
	state: () => {
		return {
			isOpenned: false,
		};
	},
	actions: {
		close() {
			localStorage.removeItem("show_share");
			this.isOpenned = false;
		},
	},
});
