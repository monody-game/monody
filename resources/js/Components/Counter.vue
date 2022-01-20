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
        <circle cx="22.5" cy="22.5" fill="none" r="20" stroke="white" />
      </svg>
      <svg class="counter__icon">
        <use :href="'/sprite.svg#' + status" />
      </svg>
    </span>
    <p class="counter__seconds">
      {{ new Date(this.time * 1000).toISOString().substr(14, 5) }}
    </p>
    <p class="counter__round">&nbsp;- {{ this.getRound }}</p>
  </div>
</template>

<script>
import CounterEmitter from "@/services/EventEmitters/CounterEmitter";
import CounterCycleService from "@/services/CounterCycleService";
import GameLifeCycleEmitter from "@/services/EventEmitters/GameLifeCycleEmitter";

const emitter = new CounterEmitter();
const gameEmitter = new GameLifeCycleEmitter();

export default {
  name: "Counter",
  data: function () {
    return {
      time: 0,
      starting_time: 0,
      counterId: "",
      status: "",
      counterService: new CounterCycleService(this.$store),
    };
  },
  mounted() {
    this.updateCircle();
    this.status = this.counterService.getState();

    if (this.starting_time !== 0) {
      this.decount();
    }

    (async () => {
      Echo.join(`game.${this.$route.params.id}`)
        .listen("game.start", () => {
          this.status = "night";
          this.time = this.counterService.getTimeCounter();
          this.starting_time = this.counterService.getTimeCounter();

          if (this.time !== 0) {
            this.updateCircle();
            this.decount();
          }
        })
        .listen("game.day", () => {
          this.status = "day";
          document
            .querySelector(".counter__icon")
            .classList.add("counter__icon-rotate");
          this.time = this.counterService.getTimeCounter();
          this.starting_time = this.counterService.getTimeCounter();

          if (this.time !== 0) {
            this.updateCircle();
            this.decount();
          }
        })
        .listen("game.night", () => {
          this.status = "night";
          this.time = this.counterService.getTimeCounter();
          this.starting_time = this.counterService.getTimeCounter();

          if (this.time !== 0) {
            this.updateCircle();
            this.decount();
          }
        });
    })();
  },
  computed: {
    getRound() {
      const rounds = {
        wait: "Attente",
        day: "Jour",
        night: "Nuit",
        vote: "Vote",
      };
      return rounds[this.status];
    },
  },
  methods: {
    decount() {
      this.counterId = window.setInterval(() => {
        /*if (this.time === this.starting_time) {
          emitter.emit("counter.start");
        }
        if (this.time !== this.starting_time && this.time % 10 === 0) {
          emitter.emit("counter.update");
        }*/

        this.time = this.time - 1;
        this.soundManagement();
        this.updateCircle();
        if (this.time === 0) {
          this.counterService.switch();
          clearInterval(this.counterId);
          //emitter.emit("counter.end");
        }
      }, 1000);
    },
    soundManagement() {
      switch (this.time) {
        case 120:
          this.playSound();
          break;
        case 60:
          this.playSound();
          break;
        case 30:
          this.playSound();
          break;
        case 10:
          this.playSound();
          break;
        case 5:
          this.playSound();
          break;
        case 3:
          this.playSound();
          break;
        case 2:
          this.playSound();
          break;
        case 1:
          this.playSound();
          break;
      }
    },
    playSound: function () {
      new Audio("../sounds/bip.mp3").play();
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
