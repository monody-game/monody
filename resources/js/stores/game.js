import {defineStore} from 'pinia'
import playerList from "../Components/PlayerList/PlayerList";

export const useStore = defineStore('game', {
  state: () => {
    return {
      playerList: [],
      currentVote: 0,
    }
  },
  actions: {
    removeGamePlayer(user) {
      const index = this.playerList.indexOf(user);
      this.playerList.splice(index, 1);
    },
    setRole(userId, role) {
      const player = this.playerList.find(player => player.id === parseInt(userId));
      if (player) {
        const index = this.playerList.indexOf(player);
        player.role = {
          group: role.team_id,
          name: role.name,
          see_has: role.display_name,
        };
        this.playerList[index] = player
      }
    },
    setVote({userID, votedBy}) {
      this.getters.getPlayerByID(userID).voted_by.push(votedBy);
      this.currentVote = userID;
    },
    unVote({userID, votedBy}) {
      if (userID === 0) {
        return;
      }
      this.currentVote = 0;
      const votes = this.getters.getPlayerByID(userID).voted_by;
      if (votes.length === 1) {
        this.getters.getPlayerByID(userID).voted_by = [];
        return;
      }
      const index = votes.indexOf(votedBy);
      votes.splice(index, 1);
    },
  },
  getters: {
    getPlayerByID: (state) => (playerID) => {
      const list = state.playerList;
      return list.filter((player) => player.id === playerID)[0] ?? {};
    },
  },
});
