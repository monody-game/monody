<template>
    <div class="play-page">
        <h1>Jouer</h1>
        <a @click="logout()" class="play-page__logout">Se déconnecter</a>
        <router-link :to="{ name: 'game', params: { id: 1 } }">Partie n°1</router-link>
        <button @click="isModalOpen = true">Créer une partie</button>
    </div>
</template>

<script>
import router from '@/Router/Router.js'
export default {
    name: 'PlayPage',
    created: function () {
        if (this.$store.getters.isUserConnected === false) {
            router.push('login')
        }
    },
    methods: {
        logout() {
            fetch('/api/auth/logout', {
                method: 'POST',
                headers: {
                    'Authorization': this.$store.getters.getAccessToken
                }
            }).then(res => {
                if(res.status === 200) {
                    this.$store.commit('removeUser')
                    router.push({ name: 'home_page' })
                }
            })
        }
    }
}

</script>

<style scoped></style>
