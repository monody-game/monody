import { defineStore } from "pinia";

export const useStore = defineStore("vocal-invitation", {
	state: () => {
		return {
			isOpenned: true,
		};
	},
	actions: {
		close() {
			this.isOpenned = false;
		}
	}
});
