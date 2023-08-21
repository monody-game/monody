import { defineStore } from "pinia";
import { useStore } from "./modal.js";

export const useAvatarWarnPopupStore = defineStore("avatar-warn-popup", {
	state: () => {
		return {
			isOpenned: false,
		};
	},
	actions: {
		close() {
			this.isOpenned = false;
			useStore().opennedModal = "profile-modal";

			return false;
		},
	},
});
