import { defineStore } from "pinia";
import { useStore as useModalStore } from "./modal.js";

export const useStore = defineStore("role-presentation", {
	state: () => {
		return {
			isOpenned: false,
		};
	},
	actions: {
		close() {
			const modalStore = useModalStore();
			this.isOpenned = false;
			modalStore.opennedModal = "game-creation-modal";
		}
	}
});
