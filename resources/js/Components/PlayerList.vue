<template>
    <div>
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
                        name: 'villager'
                    },
                    is_voted: true
                },
                {
                    id: 2,
                    username: 'John Doe',
                    avatar: '../images/avatars/2.png',
                    role: {
                        group: 'werewolf',
                        name: 'werewolf'
                    },
                    is_voted: false
                }
            ]
        }
    }
}
</script>

<style scoped>

</style>
