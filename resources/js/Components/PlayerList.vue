<template>
    <div class="player-list__">
        <Player v-for="player in players" :key="player.id" :player="player"/>
    </div>
</template>

<script>
import Player from '@/Components/Player.vue'

export default {
    name: "PlayerList",
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
                    vote: {
                        is_voted: false,
                        voted_by: []
                    }
                },
                {
                    id: 2,
                    username: 'John Doe',
                    avatar: '../images/avatars/2.png',
                    role: {
                        group: 'werewolf',
                        name: 'werewolf',
                        see_has: 'werewolf'
                    },
                    vote: {
                        is_voted: true,
                        voted_by: [
                            1
                        ]
                    }
                }
            ]
        }
    }
}
</script>

<style scoped>

</style>
