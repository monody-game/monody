import { defineStore } from "pinia";
import { useStore as useGameCreationStore } from "./game-creation-modal.js";
import { useStore as useProfileStore } from "./profile-modal.js";
import { useStore as useGameShareStore } from "./share-game-modal.js";
import { useStore as usePopupStore } from "./popup.js";
import { useStore as useRoleAssignationStore } from "./role-assignation.js";
import { useStore as useActivityConfirmationModalStore } from "./activity-confirmation-modal.js";

export const useStore = defineStore("modal", {
	state: () => {
		return {
			opennedModal: "",
		};
	},
	getters: {
		getModalStore: () => (modalName) => {
			switch (modalName) {
			case "game-creation-modal":
				return useGameCreationStore();
			case "profile-modal":
				return useProfileStore();
			case "popup":
				return usePopupStore();
			case "role-assignation":
				return useRoleAssignationStore();
			case "share-game-modal":
				return useGameShareStore();
			case "activity-confirmation-modal":
				return useActivityConfirmationModalStore();
			}
		}
	},
	actions: {
		close() {
			this.getModalStore(this.opennedModal).close();

			if (this.opennedModal !== "popup") {
				this.opennedModal = null;
			}
		},
		open(type) {
			this.getModalStore(type).isOpenned = true;
			this.opennedModal = type;
		}
	}
});