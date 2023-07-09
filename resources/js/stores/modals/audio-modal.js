import { defineStore } from "pinia";

export const useStore = defineStore("audio-management", {
	state: () => {
		return {
			isOpenned: false,
			volumes: {
				music: 7,
				ambient: 5,
			},
		};
	},
	actions: {
		close() {
			this.isOpenned = false;
		},
	},
});
