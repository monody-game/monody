<template>
  <div class="counter__main">
    <span class="counter__icon-container">
      <svg
        class="counter__icon-circle"
        height="45"
        viewBox="0 0 45 45"
        width="45"
        xmlns="http://www.w3.org/2000/svg"
      >
        <circle cx="22.5" cy="22.5" fill="none" r="20" stroke="white"/>
      </svg>
      <svg class="counter__icon">
        <use :href="'/sprite.svg#' + status"/>
      </svg>
    </span>
    <p class="counter__seconds">
      {{ new Date(this.time * 1000).toISOString().substr(14, 5) }}
    </p>
    <p class="counter__round">&nbsp;- {{ this.getRound }}</p>
  </div>
</template>

<script>
import CounterCycleService from "@/services/CounterCycleService.js";

export default {
  name: "Counter",
  data: function () {
    return {
      time: 0,
      starting_time: 0,
      counterId: "",
      status: "",
      counterService: new CounterCycleService(),
      sound: new Audio("../sounds/bip.mp3")
    };
  },
  mounted() {
    this.updateCircle();
    this.status = this.counterService.getState();

    if (this.starting_time !== 0) {
      this.decount();
    }

    Echo.join(`game.${this.$route.params.id}`)
      .listen(".game.start", () => {
        this.counterService.switch();
        this.time = this.counterService.getTimeCounter();
        this.starting_time = this.time;

        if (this.time !== 0) {
          this.updateCircle();
          this.decount();
        }
      })
      .listen(".game.newDay", () => {
        this.counterService.switch();
        this.status = this.counterService.getState();
        this.time = this.counterService.getTimeCounter();
        this.starting_time = this.counterService.getTimeCounter();

        if (this.time !== 0) {
          this.updateCircle();
          this.decount();
        }
      })
      .listen('.counter.time', (data) => {
        debugger;
        this.time = parseInt((new Date(data.start - Date.now()).getTime() / 1000).toFixed(0));
        this.starting_time = this.time;
      });
  },
  computed: {
    getRound() {
      const rounds = {
        starting: "Début de la partie",
        wait: "Attente",
        day: "Jour",
        night: "Nuit",
        vote: "Vote",
        werewolf: "Tour des Loups",
        witch: "Tour de la sorcière",
        psychic: "Tour de la voyante",
        end: "Fin de la partie",
      };
      return rounds[this.status];
    },
  },
  methods: {
    decount() {
      const channel = Echo.join(`game.${this.$route.params.id}`);
      channel.whisper('counter.start', {
        duration: this.starting_time,
        starting_timestamp: Date.now()
      })
      this.counterId = window.setInterval(() => {
        this.time = this.time - 1;
        this.soundManagement();
        this.updateCircle();

        if (this.time === 0) {
          clearInterval(this.counterId);

          channel.whisper("counter.end");
        }
      }, 1000);
    },
    soundManagement() {
      switch (this.time) {
        case 120:
          this.sound.play();
          break;
        case 60:
          this.sound.play();
          break;
        case 30:
          this.sound.play();
          break;
        case 10:
          this.sound.play();
          break;
        case 5:
          this.sound.play();
          break;
        case 3:
          this.sound.play();
          break;
        case 2:
          this.sound.play();
          break;
        case 1:
          this.sound.play();
          break;
      }
    },
    updateCircle() {
      const circle = document.querySelector(".counter__icon-circle circle");
      if (circle) {
        let percentage = (this.time / this.starting_time) * 100;

        if (this.starting_time === 0) {
          percentage = 100;
        }

        const circumference = Math.PI * 2 * 20;
        const offset = (circumference * percentage) / 100 - circumference;

        circle.style.strokeDasharray = `${circumference}, ${circumference}`;
        circle.style.strokeDashoffset = `${offset}`;
      }
    },
  },
  beforeRouteLeave(to, from, next) {
    clearInterval(this.counterId);
    next();
  },
};
</script>

<style scoped></style>
