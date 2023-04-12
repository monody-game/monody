import { defineStore } from "pinia";
import { useStore as useGameCreationStore } from "./game-creation-modal.js";
import { useStore as useProfileStore } from "./profile-modal.js";
import { useStore as useGameShareStore } from "./share-game-modal.js";
import { useStore as usePopupStore } from "./popup.js";
import { useStore as useRoleAssignationStore } from "./role-assignation.js";
import { useStore as useActivityConfirmationModalStore } from "./activity-confirmation-modal.js";
import { useStore as useGameDetailsModalStore } from "./game-details.js";
import { useStore as useShareProfleStore } from "./share-profile-modal.js";
import { useStore as useBadgesModalStore } from "./badges.js";

export const useStore = defineStore("modal", {
	state: () => {
		return {
			opennedModal: "popup",
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
			case "game-details":
				return useGameDetailsModalStore();
			case "share-profile":
				return useShareProfleStore();
			case "badges":
				return useBadgesModalStore();
			}
		}
	},
	actions: {
		close() {
			if (!this.opennedModal) return;
			this.getModalStore(this.opennedModal).close();

			if (this.opennedModal !== "popup") {
				this.opennedModal = "popup";
			}

			if (document.body.classList.contains("overflow-hidden")) {
				document.body.classList.remove("overflow-hidden");
			}
		},
		open(type) {
			window.scrollTo(0, 0);
			this.getModalStore(type).isOpenned = true;
			this.opennedModal = type;
			document.body.classList.add("overflow-hidden");
		}
	}
});
