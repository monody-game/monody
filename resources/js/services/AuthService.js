import { useStore } from '../stores/user';

export default class AuthService {

  constructor() {
    this.store = useStore();
  }

  check () {
    return this.store.access_token !== "" || this.isAccessTokenSaved();
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

  async getUserIfAccessToken () {
    if (this.store.access_token === "" && this.isAccessTokenSaved()) {
      const access_token = this.getAccessToken();

      const res = await JSONFetch("/user", "GET")
      const data = res.data

      if(!data) {
        return false;
      }

      this.store.setUser({
        id: data.id,
        username: data.username,
        avatar: data.avatar,
        access_token: access_token,
      });
    }
    return true;
  }

  async logout () {
    await fetch("/api/auth/logout", {
      method: "POST",
      headers: {
        Authorization: "Bearer " + this.getAccessToken(),
      },
    });
    this.store.$reset;
    sessionStorage.removeItem('access-token');
    localStorage.removeItem('access-token');
  }
}
