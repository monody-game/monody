<template>
    <div class="play-page">
        <h1>Jouer</h1>
        <router-link :to="{ name: 'game', params: { id: 1 } }">Partie n°1</router-link>
    </div>
</template>

<script>
export default {
    name: 'PlayPage'
}
</script>

<style scoped></style>
