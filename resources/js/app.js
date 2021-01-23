import Vue from 'vue'
import App from '@/App.vue'
import router from '@/Router/Router'

require('./bootstrap')

new Vue({
    router,
    el: '#app',
    render: h => h(App)
})
