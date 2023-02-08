import { defineStore } from "pinia";

export const useStore = defineStore("role-assignation", {
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
