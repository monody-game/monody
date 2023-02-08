import { defineStore } from "pinia";

export const useStore = defineStore("profile-modal", {
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
