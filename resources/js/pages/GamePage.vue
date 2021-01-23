<template>
    <div>
        <router-link to="/play">Accueil</router-link>
        Partie n° {{ $route.params.id }}
        <Counter start_time="60" />
    </div>
</template>

<script>
import Counter from '@/Components/Counter.vue'

const Pusher = require('pusher-js')

window.Pusher = new Pusher('b10f500926faee2fbbea', {
    cluster: process.env.PUSHER_CLUSTER
})

window.Pusher.logToConsole = true

let channel = window.Pusher.subscribe('messages');
channel.bind('send', function(data) {
    alert(JSON.stringify(data));
});

export default {
    name: 'GamePage',
    components: {
        Counter: Counter
    }
}
</script>

<style scoped></style>
