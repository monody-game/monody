import { defineStore } from "pinia";

export const useStore = defineStore("popup", {
	state: () => {
		return {
			isOpenned: false,
			type: "success",
			content: "",
			note: ""
		};
	},
});
