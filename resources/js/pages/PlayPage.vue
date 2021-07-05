<template>
    <div class="play-page">
        <h1>Jouer</h1>
        <router-link :to="{ name: 'game', params: { id: 1 } }">Partie n°1</router-link>
        <button @click="isModalOpen = true">Créer une partie</button>
    </div>
</template>

<script>
import router from '@/Router/Router.js'

export default {
    name: 'PlayPage',
    data() {
        return {
            isModalOpen: false
        }
    },
    created: function () {
        if (this.$store.getters.isUserConnected === false) {
            router.push('login')
        }
    },
    methods: {
        createGame: function () {},
        cancel: function () {}
    }
}

</script>

<style scoped></style>
