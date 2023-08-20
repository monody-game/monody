import { defineStore } from "pinia";

export const useAvatarWarnPopupStore = defineStore("avatar-warn-popup", {
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
