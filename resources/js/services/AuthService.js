import UserStore from "@/store/UserStore.js";

export default class AuthService {
  /**
   * @param {UserStore} store
   */
  check (store) {
    return store.getters.isAccessTokenSet || this.isAccessTokenSaved();
  }

  isAccessTokenSaved () {
    return localStorage.getItem('access-token') || sessionStorage.getItem('access-token');
  }

  getAccessToken () {
    if (localStorage.hasOwnProperty('access-token')) {
      return localStorage.getItem('access-token');
    } else if (sessionStorage.hasOwnProperty('access-token')) {
      return sessionStorage.getItem('access-token');
    }
  }

  async getUserIfAccessToken (store) {
    if (store.getters.isAccessTokenSet === false && this.isAccessTokenSaved()) {
      const access_token = this.getAccessToken();

      const res = await JSONFetch("/user", "GET")
      const data = res.data

      if(!data) {
        return false;
      }

      store.commit("setUser", {
        id: data.id,
        username: data.username,
        avatar: data.avatar,
        access_token: access_token,
      });
    }
    return true;
  }

  /**v
   * @param {UserStore} store
   */
  async logout (store) {
    await fetch("/api/auth/logout", {
      method: "POST",
      headers: {
        Authorization: "Bearer " + this.getAccessToken(),
      },
    });
    store.commit("removeUser");
    sessionStorage.removeItem('access-token');
    localStorage.removeItem('access-token');
  }
}
