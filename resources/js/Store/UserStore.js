import Vuex from 'vuex'
import Vue from 'vue'

Vue.use(Vuex)

const defaultState = {
    id: 0,
    username: '',
    avatar: '',
    is_connected: false,
    access_token: ''
}

const UserStore = new Vuex.Store({
    strict: true,
    state: {
        user: defaultState
    }, 
    mutations: {
        setUser (state, user) {
            state.user = user
        },
        removeUser (state) {
            Object.assign(state.user, defaultState)
        }
    },
    getters: {
        isUserConnected: state => {
            return state.user.is_connected
        },
        getAccessToken: state => {
            return 'Bearer ' + state.user.access_token
        }
    }
})


export default UserStore