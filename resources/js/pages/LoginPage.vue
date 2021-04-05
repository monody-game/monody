<template>
    <div class="login-page">
        <h1>Se connecter</h1>
        <form class="login-page__form">
            <input type="text" class="login-page__input" placeholder="Nom d'utilisateur" v-model="username" required>
            <input type="password" class="login-page__input" placeholder="Mot de passe" v-model="password" required>
            <div>
                <input type="submit" value="Se connecter" @click.prevent="login()">
                <router-link to="/register">Pas encore de compte ?</router-link>
            </div>
        </form>
    </div>
</template>

<script>
import 'axios'

export default {
    name: 'LoginPage',
    data() {
        return {
            username: '',
            password: ''
        }
    },
    methods: {
        login: function () {
            axios.post('/api/auth/login', {
                body: JSON.stringify({
                    username: this.username,
                    password: this.password
                })
            }).then((res) => {
                if(res.data === true) {

                }
            })
        }
    }
}
</script>

<style scoped></style>
