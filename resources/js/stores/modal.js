import { defineStore } from "pinia";
import { useStore as useGameCreationStore } from "./GameCreationModal.js";
import { useStore as useProfileStore } from "./ProfileModal.js";
import { useStore as usePopupStore } from "./popup.js";

export const useStore = defineStore("modal", {
	state: () => {
		return {
			opennedModal: null,
		};
	},
	getters: {
		getModalStore: () => (modalName) => {
			switch (modalName) {
			case "GameCreationModal":
				return useGameCreationStore();
			case "ProfileModal":
				return useProfileStore();
			case "popup":
				return usePopupStore();
			}
		}
	},
	actions: {
		close(state) {
			this.getModalStore(state.opennedModal).close();
		}
	}
});
