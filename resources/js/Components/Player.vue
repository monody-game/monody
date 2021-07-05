<template>
    <div class="player__container">
        <div class="player__avatar-container">
            <span class="player__voters" v-if="isVoted()">voté</span>
            <img :src="player.avatar" alt="" class="player__avatar" :class="isVoted() === true ? 'player__is-voted' : ''">
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
        },
        isVoted: function () {
            return this.player.voted_by.length > 0;
        }
    }
}
</script>

<style scoped>

</style>
