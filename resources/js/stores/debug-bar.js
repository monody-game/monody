import { defineStore } from "pinia";

export const useStore = defineStore("debug-bar", {
	state: () => {
		return {
			errors: [],
			warns: [],
		};
	},
});
