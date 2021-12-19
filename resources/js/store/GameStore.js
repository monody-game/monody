const GameStore = {
  state: {
    game: {
      playerList: [],
      currentVote: 0,
    },
  },
  mutations: {
    setGamePlayers (state, users) {
      state.game.playerList = users;
    },
    addGamePlayer (state, user) {
      state.game.playerList.push(user);
    },
    removeGamePlayer (state, user) {
      const index = state.game.playerList.indexOf(user);
      state.game.playerList.splice(index, 1);
    },
    clearGamePlayers (state) {
      state.game.playerList = [];
    },
    setVote (state, { userID, votedBy }) {
      this.getters.getPlayerByID(userID).voted_by.push(votedBy);
      state.game.currentVote = userID;
    },
    unVote (state, { userID, votedBy }) {
      if (userID === 0) {
        return;
      }
      state.game.currentVote = 0;
      const votes = this.getters.getPlayerByID(userID).voted_by;
      if (votes.length === 1) {
        this.getters.getPlayerByID(userID).voted_by = [];
        return;
      }
      const index = votes.indexOf(votedBy);
      votes.splice(index, 1);
    },
    resetCurrentVote (state) {
      state.game.currentVote = 0;
    },
  },
  getters: {
    getPlayerList: (state) => state.game.playerList,
    getPlayerByID: (state, getters) => (playerID) => {
      const list = getters.getPlayerList;
      return list.filter((player) => player.id === playerID)[0] ?? {};
    },
    getCurrentVote: (state) => state.game.currentVote,
  },
};

export default GameStore;
