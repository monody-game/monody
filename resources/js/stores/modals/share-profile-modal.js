import { defineStore } from "pinia";

export const useStore = defineStore("share-profile", {
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
