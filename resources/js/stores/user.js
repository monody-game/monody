import {defineStore} from 'pinia'

export const useStore = defineStore('user', {
  state: () => ({
    id: 0,
    username: "",
    avatar: "",
    level: 0,
    exp: 0,
  }),
  actions: {
    setUser(payload) {
      this.id = payload.id
      this.username = payload.username
      this.avatar = payload.avatar
      this.level = payload.level
      this.exp = payload.exp
    },
  },
  getters: {
    getUser() {
      return {
        id: this.id,
        username: this.username,
        avatar: this.avatar,
        level: this.level,
        exp: this.exp
      }
    }
  }
});
