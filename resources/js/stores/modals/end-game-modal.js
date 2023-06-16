import { defineStore } from "pinia";

export const useStore = defineStore("end-game-modal", {
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
