import { defineStore } from 'pinia'

export const useStore = defineStore('game',{
  state: () => {
    return {
      playerList: [],
      currentVote: 0,
    }
  },
  actions: {
    removeGamePlayer (state, user) {
      const index = state.playerList.indexOf(user);
      state.playerList.splice(index, 1);
    },
    setVote (state, { userID, votedBy }) {
      this.getters.getPlayerByID(userID).voted_by.push(votedBy);
      state.currentVote = userID;
    },
    unVote (state, { userID, votedBy }) {
      if (userID === 0) {
        return;
      }
      state.currentVote = 0;
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
