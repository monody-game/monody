import UserStore from "@/Store/UserStore.js";

export default class AuthService {
    /**
     * @param {UserStore} store
     */
    check(store) {
        if (
            store.getters.isUserConnected !== true ||
            !this.isAccessTokenSaved()
        ) {
            return false
        }
    }

    isAccessTokenSaved() {
        if (
            localStorage.getItem("monody_access-token") ||
            sessionStorage.getItem("monody_access-token")
        ) {
            return true;
        }
        return false;
    }
}
