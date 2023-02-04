import { defineStore } from "pinia";

export const useStore = defineStore("game", {
	state: () => {
		return {
			playerList: [],
			state: 0,
			currentInteractionId: "",
			availableActions: {},
			playerRefs: [],
			roles: []
		};
	},
	actions: {
		setRole(userId, role) {
			const player = this.playerList.find(listItem => listItem.id === userId);
			if (player) {
				const index = this.playerList.indexOf(player);
				player.role = {
					team_id: role.team_id,
					name: role.name,
					display_name: role.display_name,
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
