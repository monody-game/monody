const ModalStore = {
  state: {
    modal: {
      isOpenned: false,
      selectedRoles: [],
      roles: [],
      teams: [],
      errors: [],
    },
  },
  mutations: {
    addError (state, message) {
      state.modal.errors.push(message);
    },
    removeErrors (state) {
      state.modal.errors = [];
    },
    closeModal (state) {
      state.modal.isOpenned = false;
      state.modal.selectedRoles = [];
    },
    openModal (state) {
      state.modal.isOpenned = true;
    },
    addSelectedRole (state, roleId) {
      state.modal.selectedRoles.push(roleId);
    },
    removeSelectedRole (state, roleId) {
      const selectedRoles = state.modal.selectedRoles;
      const index = selectedRoles.indexOf(roleId);
      selectedRoles.splice(index, 1);
    },
    setRoles (state, roles) {
      state.modal.roles = roles;
    },
    setTeams (state, teams) {
      state.modal.teams = teams;
    },
  },
  getters: {
    isModalOpenned: (state) => {
      return state.modal.isOpenned;
    },
    getSelectedRoles: (state) => {
      return state.modal.selectedRoles;
    },
    getRoles: (state) => {
      return state.modal.roles;
    },
    getRoleCountById: (state, getters) => (id) => {
      const selectedRoles = getters.getSelectedRoles;
      let count = 0;
      selectedRoles.forEach((role) => {
        if (role === id) {
          count++;
        }
      });
      return count;
    },
    getTeams: (state) => {
      return state.modal.teams;
    },
    getTeamById: (state, getters) => (id) => {
      return getters.getTeams.filter((team) => team.id === id)[0];
    },
    getErrors (state) {
      return state.modal.errors;
    }
  },
};

export default ModalStore;
