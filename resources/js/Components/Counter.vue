<template>
    <div>
        <p>{{ (new Date(this.time * 1000)).toISOString().substr(14, 5) }}</p>
        <button @click="time = time + 30">+30s</button>
        <button @click="time = time - 10">-10s</button>
    </div>
</template>

<script>
export default {
    name: 'Counter',
    props: {
        start_time: {
            default: 0
        }
    },
    created () {
        this.decount()
    },
    data: function () {
        return {
            time: parseInt(this.start_time)
        }
    },
    methods: {
        decount: function () {
            window.setInterval(() => {
                if (this.time === 0) {
                    return
                }
                this.time = this.time - 1
                switch (this.time) {
                    case 31:
                        console.log(this.time)
                        this.playDing()
                }
            }, 1000)
        },
        playDing: function () {
            let audio = new Audio('../sounds/bip.mp3')
            audio.play()
        }
    }
}
</script>

<style scoped></style>
