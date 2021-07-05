import Vue from 'vue'
import App from '@/App.vue'
import router from '@/Router/Router'
import UserStore from '@/Store/UserStore'

require('./bootstrap.js')

new Vue({
    router,
    el: '#app',
    store: UserStore,
    render: h => h(App)
})
