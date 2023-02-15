import { defineStore } from "pinia";

export const useStore = defineStore("activity-confirmation-modal", {
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
