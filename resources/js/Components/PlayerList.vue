<template>
    <div class="player-list__" :data-token="token">
        <Player v-for="player in players" :key="player.id" :player="player"/>
    </div>
</template>

<script>
import Player from '@/Components/Player.vue'

export default {
    name: "PlayerList",
    props: ['token'],
    components: {
        Player: Player
    },
    data() {
        return {
            players: [
                {
                    id: 1,
                    username: 'moon250',
                    avatar: '../images/avatars/1.png',
                    role: {
                        group: 'villager',
                        name: 'villager',
                        see_has: 'villager'
                    },
                    voted_by: []
                },
                {
                    id: 2,
                    username: 'John Doe',
                    avatar: '../images/avatars/default.png',
                    role: {
                        group: 'werewolf',
                        name: 'werewolf',
                        see_has: 'werewolf'
                    },
                    voted_by: [
                        1
                    ]
                }
            ]
        }
    }
}
</script>

<style scoped>

</style>
