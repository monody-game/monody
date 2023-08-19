import { defineStore } from "pinia";
import { useStore as useGameCreationStore } from "./game-creation-modal.js";
import { useStore as useProfileStore } from "./profile-modal.js";
import { useStore as useGameShareStore } from "./share-game-modal.js";
import { useStore as usePopupStore } from "./popup.js";
import { useStore as useRoleAssignationStore } from "./role-assignation.js";
import { useStore as useActivityConfirmationModalStore } from "./activity-confirmation-modal.js";
import { useStore as useGameDetailsModalStore } from "./game-details.js";
import { useStore as useShareProfileStore } from "./share-profile-modal.js";
import { useStore as useBadgesModalStore } from "./badges.js";
import { useStore as useLeaderboardsModalStore } from "./leaderboards.js";
import { useStore as useLogoutWarnPopupStore } from "./logout-warn-popup.js";
import { useStore as useRolePresentationStore } from "./role-presentation.js";
import { useStore as useEndGameModalStore } from "./end-game-modal.js";
import { useStore as useAudioManagementModalStore } from "./audio-modal.js";
import { useStatisticsModal } from "./statistics-modal.js";

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
					return useShareProfileStore();
				case "badges":
					return useBadgesModalStore();
				case "leaderboards":
					return useLeaderboardsModalStore();
				case "logout-warn-popup":
					return useLogoutWarnPopupStore();
				case "role-presentation":
					return useRolePresentationStore();
				case "end-game-modal":
					return useEndGameModalStore();
				case "audio-management":
					return useAudioManagementModalStore();
				case "statistics-modal":
					return useStatisticsModal();
			}
		},
	},
	actions: {
		close() {
			if (!this.opennedModal) return;
			const popup = this.getModalStore(this.opennedModal).close();

			if ((popup ?? true) && this.opennedModal !== "popup") {
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
		},
	},
});
