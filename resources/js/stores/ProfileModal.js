import { defineStore } from "pinia";

export const useStore = defineStore("ProfileModal", {
	state: () => {
		return {
			isOpenned: false,
		};
	},
});
