import { defineStore } from "pinia";

export const useStore = defineStore("role-assignation", {
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
