import { defineStore } from "pinia";
import { useStore as useModalStore } from "./modal.js";

export const useStore = defineStore("role-presentation", {
	state: () => {
		return {
			isOpenned: false,
			opennedModal: "",
			role: {},
		};
	},
	actions: {
		close() {
			const modalStore = useModalStore();
			this.isOpenned = false;
			this.role = false;
			modalStore.opennedModal = this.opennedModal;
			this.opennedModal = "";

			return false;
		},
	},
});
