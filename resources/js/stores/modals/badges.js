import { defineStore } from "pinia";

export const useStore = defineStore("badges", {
	state: () => {
		return {
			isOpenned: false,
			badges: []
		};
	},
	actions: {
		close() {
			this.isOpenned = false;
		}
	}
});
