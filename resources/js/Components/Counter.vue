<template>
    <div class="counter__main">
        <p class="counter__seconds">{{ (new Date(this.time * 1000)).toISOString().substr(14, 5) }}&nbsp;</p>
        <p class="counter__round">- {{ round }}</p>
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
            time: parseInt(this.start_time),
            round: 'Jour'
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
                    case 60:
                        this.playDing()
                        break
                    case 30:
                        this.playDing()
                        break
                    case 10:
                        this.playDing()
                        break
                    case 5:
                        this.playDing()
                        break
                    case 3:
                        this.playDing()
                        break
                    case 2:
                        this.playDing()
                        break
                    case 1:
                        this.playDing()
                        break
                }
            }, 1000)
        },
        playDing: function () {
            let audio = new Audio('../sounds/bip.mp3')
            console.log('played sound')
            audio.play()
        }
    }
}
</script>

<style scoped></style>
