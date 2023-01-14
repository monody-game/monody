import { defineStore } from "pinia";

export const useStore = defineStore("game", {
	state: () => {
		return {
			playerList: [],
			state: 0,
			currentInteractionId: "",
			availableActions: {},
			playerRefs: []
		};
	},
	actions: {
		setRole(userId, role) {
			const player = this.playerList.find(listItem => listItem.id === userId);
			if (player) {
				const index = this.playerList.indexOf(player);
				player.role = {
					group: role.team_id,
					name: role.name,
					see_has: role.display_name,
				};
				this.playerList[index] = player;
			}
		},
	},
	getters: {
		getPlayerByID: (state) => (playerID) => {
			const list = state.playerList;
			return list.filter((player) => player.id === playerID)[0] ?? {};
		}
	},
});
