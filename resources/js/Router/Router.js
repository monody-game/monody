import Vue from 'vue'
import VueRouter from 'vue-router'

Vue.use(VueRouter)

const routes = [
    {
        path: '/',
        name: 'home',
        component: require('@/pages/HomePage.vue').default
    },
    {
        path: '/login',
        name: 'login',
        component: require('@/pages/LoginPage.vue').default
    },
    {
        path: '/play',
        name: 'play',
        component: require('@/pages/PlayPage.vue').default
    },
    {
        path: '/game/:id',
        name: 'game',
        component: require('@/pages/GamePage.vue').default
    },
    {
        path: '*',
        name: '404',
        component: require('@/pages/404.vue').default
    }
]

const router = new VueRouter({
    mode: 'history',
    routes
})

export default router
