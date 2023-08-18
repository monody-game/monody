import { defineStore } from "pinia";

export const useStore = defineStore("leaderboards", {
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
