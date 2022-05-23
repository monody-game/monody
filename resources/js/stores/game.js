import { defineStore } from 'pinia'
import { useStore as useUserStore } from "./user";

const VILLAGER_GROUP = 1;
const WEREWOLF_GROUP = 2;
const SOLO_GROUP = 3;

export const useStore = defineStore('game', {
  state: () => {
    return {
      playerList: [],
      currentVote: 0,
      state: "GAME_WAITING"
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
      this.getPlayerByID(userID).voted_by.push(votedBy);
      this.currentVote = userID;
    },
    unVote({userID, votedBy}) {
      if (userID === 0) {
        return;
      }
      this.currentVote = 0;
      const votes = this.getPlayerByID(userID).voted_by;
      if (votes.length === 1) {
        this.getPlayerByID(userID).voted_by = [];
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
    isWerewolf() {
      const id = useUserStore().id;
      const player = this.getPlayerByID(id);

      return player.role.group === WEREWOLF_GROUP;
    }
  },
});
