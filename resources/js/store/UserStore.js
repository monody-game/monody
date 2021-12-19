const defaultState = {
  id: 0,
  username: "",
  avatar: "",
  access_token: "",
};

const UserStore = {
  state: {
    user: defaultState,
  },
  mutations: {
    setUser (state, user) {
      state.user = user;
    },
    removeUser (state) {
      Object.assign(state.user, defaultState);
    },
  },
  getters: {
    isAccessTokenSet: (state) => {
      return state.user.access_token !== "";
    },
    getAccessToken: (state) => {
      return "Bearer " + state.user.access_token;
    },
    getUsername: (state) => {
      return state.user.username;
    },
    getAvatar: (state) => {
      return state.user.avatar;
    },
    getUserId: (state) => {
      return state.user.id;
    },
    getUser: (state) => {
      return state.user;
    },
  },
};

export default UserStore;
