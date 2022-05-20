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
        <use :href="'/sprite.svg#' + getIcon"/>
      </svg>
    </span>
    <p class="counter__seconds">
      {{ new Date(this.time * 1000).toISOString().substr(14, 5) }}
    </p>
    <p class="counter__round">&nbsp;- {{ this.getRound }}</p>
  </div>
</template>

<script>
import CounterCycleService from "../services/CounterCycleService.js";
import ChatService from "../services/ChatService";

export default {
  name: "Counter",
  data: function () {
    return {
      time: 0,
      startingTime: 0,
      totalTime: 0,
      counterId: "",
      status: "GAME_WAITING",
      counterService: new CounterCycleService(),
      chatService: new ChatService(),
      sound: new Audio("../sounds/bip.mp3")
    };
  },
  mounted() {
    this.updateCircle();
    Echo.join(`game.${this.$route.params.id}`)
      .listen('.game.state', (data) => {
        console.log('game.state');
        console.log(data);
        if (data) {
          clearInterval(this.counterId);
          this.time = data.counterDuration === -1 ? 0 : data.counterDuration;
          this.startingTime = data.startTimestamp;
          this.totalTime = this.time;
          this.status = data.state;
          this.updateCircle();
          this.decount();
          this.updateOverlay()
        }
      })
  },
  computed: {
    getRound() {
      const rounds = {
        GAME_WAITING: "Attente",
        GAME_STARTING: "Début de la partie",
        GAME_NIGHT: "Nuit",
        GAME_WEREWOLF: "Tour des Loups",
        GAME_DAY: "Jour",
        GAME_VOTE: "Vote",
      };
      return rounds[this.status];
    },
    getIcon() {
      const icons = {
        GAME_WAITING: "wait",
        GAME_STARTING: "wait",
        GAME_NIGHT: "night",
        GAME_WEREWOLF: "night",
        GAME_DAY: "day",
        GAME_VOTE: "vote",
      };
      return icons[this.status];
    }
  },
  methods: {
    decount() {
      if (this.time === 0) {
        return;
      }
      this.counterId = window.setInterval(() => {
        this.time = this.time - 1;
        this.soundManagement();
        this.updateCircle();

        if (this.time === 0) {
          clearInterval(this.counterId);
        }
      }, 1000);
    },
    soundManagement() {
      switch (this.time) {
        case 120:
        case 60:
        case 30:
        case 10:
        case 5:
        case 3:
        case 2:
        case 1:
          this.sound.play();
          break;
      }
    },
    updateCircle() {
      const circle = document.querySelector(".counter__icon-circle circle");
      if (circle) {
        let percentage = (this.time / this.totalTime) * 100;

        if (this.totalTime === 0) {
          percentage = 100;
        }

        const circumference = Math.PI * 2 * 20;
        const offset = (circumference * percentage) / 100 - circumference;

        circle.style.strokeDasharray = `${circumference}, ${circumference}`;
        circle.style.strokeDashoffset = `${offset}`;
      }
    },
    updateOverlay() {
      switch (this.status) {
        case "GAME_WAITING":
        case "GAME_STARTING":
          break;
        case "GAME_NIGHT":
          this.counterService.onNight()
          this.chatService.timeSeparator("Tombée de la nuit");
          break;
        case "GAME_WEREWOLF":
          this.counterService.onNight()
          break;
        case "GAME_DAY":
          this.counterService.onDay()
          this.chatService.timeSeparator("Lever du jour");
          break;
        case "GAME_VOTE":
          this.counterService.onDay()
          break;
      }
    }
  },
  beforeRouteLeave(to, from, next) {
    clearInterval(this.counterId);
    next();
  },
};
</script>

<style scoped></style>
