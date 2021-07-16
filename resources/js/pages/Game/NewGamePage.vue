<template>
    <div></div>
</template>
<script>
import AuthService from "@/services/AuthService.js";

export default {
    name: "NewGamePage",
    created() {
        console.log(sessionStorage, localStorage);
        const auth = new AuthService();
        if (!auth.check(this.$store, this.$router)) {
            this.$router.push({ name: "login" });
        }
    }
};
</script>
<style scoped></style>
