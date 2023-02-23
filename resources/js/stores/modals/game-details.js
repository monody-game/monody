import { defineStore } from "pinia";

export const useStore = defineStore("game-details", {
	state: () => {
		return {
			isOpenned: false,
		};
	},
	actions: {
		close() {
			this.isOpenned = false;
		}
	}
});
