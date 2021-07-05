import Vuex from 'vuex'
import Vue from 'vue'

Vue.use(Vuex)

const UserStore = new Vuex.Store({
    strict: true,
    state: {
        user: {
            id: 0,
            username: '',
            avatar: '',
            is_connected: false
        }
    }, 
    mutations: {
        setUser (state, user) {
            state.user = user
        }
    },
    getters: {
        isUserConnected: state => {
            return state.user.is_connected
        }
    }
})


export default UserStore