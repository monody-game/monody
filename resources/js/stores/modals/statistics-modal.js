import { defineStore } from "pinia";

export const useStatisticsModal = defineStore("statistics-modal", {
	state: () => {
		return {
			isOpenned: false,
		};
	},
	actions: {
		close() {
			localStorage.removeItem("show_share");
			this.isOpenned = false;
		},
	},
});
