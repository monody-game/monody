<template>
    <div class="login-page">
        <h1>Se connecter</h1>
        <form class="login-page__form">
            <input type="text" class="login-page__input" placeholder="Nom d'utilisateur" v-model="username" required>
            <input type="password" class="login-page__input" placeholder="Mot de passe" v-model="password" required>
            <label for="remember_me">Se souvenir de moi</label>
            <input id="remember_me" v-model="remember_me" class="login-page__remember-me" type="checkbox">
            <div>
                <input type="submit" value="Se connecter" @keyup.enter="login()" @click.prevent="login()">
                <router-link to="/register">Pas encore de compte ?</router-link>
            </div>
        </form>
    </div>
</template>

<script>
const axios = require('axios').default
import router from '@/Router/Router.js'

export default {
    name: 'LoginPage',
    data() {
        return {
            username: '',
            password: '',
            remember_me: false
        }
    },
    methods: {
        login: function () {
            axios.post('/api/login', {
                username: this.username,
                password: this.password,
                remember_me: this.remember_me
            }).then((res) => {
                if (res.status === 200) {
                    this.$store.commit('setUser', {
                        id: res.data.user.id,
                        username: res.data.user.username,
                        avatar: res.data.user.avatar,
                        is_connected: true
                    })
                    router.push('play')
                }
            }).catch((res) => {
                if (res.status === 401) {
                    console.error('Erreur dans les identifiants !')
                }
            })
        }
    }
}
</script>

<style scoped></style>
