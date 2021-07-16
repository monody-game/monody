<template>
    <div class="play-page">
        <h1>Jouer</h1>
        <a @click="logout()" class="play-page__logout">Se déconnecter</a>
        <router-link :to="{ name: 'game', params: { id: 1 } }"
            >Partie n°1</router-link
        >
        <button @click="isModalOpen = true">Créer une partie</button>
    </div>
</template>

<script>
import AuthService from "@/services/AuthService.js";

export default {
    name: "PlayPage",
    created: function() {
        console.log(sessionStorage, localStorage);
        const auth = new AuthService();
        if (!auth.check(this.$store, this.$router)) {
            this.$router.push({ name: "login" });
        }
    },
    methods: {
        logout() {
            fetch("/api/auth/logout", {
                method: "POST",
                headers: {
                    Authorization: this.$store.getters.getAccessToken
                }
            }).then(res => {
                if (res.status === 200) {
                    this.$store.commit("removeUser");
                    sessionStorage.removeItem("monody_access-token");
                    localStorage.removeItem("monody_access-token");
                    this.$router.push({ name: "home_page" });
                }
            });
        }
    }
};
</script>

<style scoped></style>
