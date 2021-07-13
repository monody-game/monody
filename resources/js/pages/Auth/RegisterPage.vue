<template>
    <div class="register-page">
        <h1>S'inscrire</h1>
        <form class="register-page__form">
            <input type="text" class="register-page__input" placeholder="Nom d'utilisateur" v-model="username" required>
            <input type="email" class="register-page__input" placeholder="Email" v-model="email" required>
            <input type="password" class="register-page__input" placeholder="Mot de passe" v-model="password" required>
            <input type="password" class="register-page__input" placeholder="Confirmez le mot de passe" v-model="password_confirmation" required>
            <div>
                <input type="submit" value="Se connecter" @keyup.enter="login()" @click.prevent="login()">
            </div>
        </form>
    </div>
</template>

<script>
const axios = require('axios').default
import router from '@/Router/Router.js'

export default {
    name: 'RegisterPage',
    data() {
        return {
            username: '',
            email: '',
            password: '',
            password_confirmation: ''
        }
    },
    methods: {
        login: function () {
            axios.post('/api/auth/register', {
                username: this.username,
                email: this.email,
                password: this.password,
                password_confirmation: this.password_confirmation
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
