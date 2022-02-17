import { defineStore } from 'pinia'

export const useStore = defineStore('user', {
  state: () => ({
      id: 0,
      username: "",
      avatar: "",
      access_token: "",
      level: 0,
      exp: 0,
  }),
  actions: {
    setUser(payload) {
      this.id = payload.id
      this.username = payload.username
      this.avatar = payload.avatar
      this.access_token = payload.access_token
      this.level = payload.level
      this.exp = payload.exp
    },
  }
});
