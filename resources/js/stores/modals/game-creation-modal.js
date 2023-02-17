import { defineStore } from "pinia";

export const useStore = defineStore("game-creation-modal", {
	state: () => {
		return {
			isOpenned: false,
			selectedRoles: [],
			roles: [],
			teams: [],
			gameId: null
		};
	},
	getters: {
		getRoleCountById: (state) => {
			return (id) => {
				let count = 0;
				state.selectedRoles.forEach((role) => {
					if (role === id) {
						count++;
					}
				});
				return count;
			};
		},
		getTeamById: (state) => {
			return (id) => {
				return state.teams.filter((team) => team.id === id)[0];
			};
		},
	},
	actions: {
		removeSelectedRole (roleId) {
			const selectedRoles = this.selectedRoles;
			const index = selectedRoles.indexOf(roleId);
			selectedRoles.splice(index, 1);
		},
		close () {
			this.isOpenned = false;
			this.selectedRoles = [];
			this.gameId = null;
			document.documentElement.style.removeProperty(
				"--villager-balance-width"
			);
			document.documentElement.style.removeProperty(
				"--werewolf-balance-width"
			);
		}
	}
});