<template>
    <div class="player__container">
        <div class="player__avatar-container">
            <img :src="player.avatar" alt="" class="player__avatar" :class="player.is_voted === true ? 'player__is-voted' : ''">
            <span class="player__is-wolf" v-if="isWerewolf()"></span>
        </div>
        <p class="player__username">{{ player.username }}</p>
    </div>
</template>

<script>
export default {
    name: "Player",
    props: ['player'],
    methods: {
        isWerewolf: function () {
            return this.player.role.group === 'werewolf';
        }
    }
}
</script>

<style scoped>

</style>
