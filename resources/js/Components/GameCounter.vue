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
        <circle
          cx="22.5"
          cy="22.5"
          fill="none"
          r="20"
          stroke="white"
        />
      </svg>
      <svg class="counter__icon">
        <use :href="'/sprite.svg#' + icon" />
      </svg>
    </span>
    <p class="counter__seconds">
      {{ new Date(time * 1000).toISOString().substr(14, 5) }}
    </p>
    <p class="counter__round">
      &nbsp;- {{ roundText }}
    </p>
  </div>
</template>

<script>
import CounterCycleService from "../services/CounterCycleService.js";
import ChatService from "../services/ChatService";
import { useStore } from "../stores/game";

export default {
	name: "GameCounter",
	data() {
		return {
			time: 0,
			startingTime: 0,
			totalTime: 0,
			counterId: "",
			status: 0,
			counterService: new CounterCycleService(),
			chatService: new ChatService(),
			sound: new Audio("../sounds/bip.mp3"),
			roundText: this.getRound(),
			round: 0,
			icon: ""
		};
	},
	async mounted() {
		this.updateCircle();
		let state = await this.getState();
		this.sound.load();
		this.roundText = state.name;
		this.icon = state.icon;

		window.Echo.join(`game.${this.$route.params.id}`)
			.listen(".game.state", async (data) => {
				if (data) {
					clearInterval(this.counterId);
					this.time = data.counterDuration === -1 ? 0 : data.counterDuration;
					this.startingTime = data.startTimestamp;
					this.totalTime = this.time;
					this.status = data.state;
					this.round = data.round;
					state = await this.getState(data.state);
					this.roundText = state.name;
					this.icon = state.icon;
					useStore().state = data.state;
					this.updateCircle();
					this.decount();
					this.updateOverlay();
				}
			});
	},
	beforeUnmount() {
		clearInterval(this.counterId);
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
				this.sound.currentTime = 0;
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
			case 0:
			case 1:
				break;
			case 2:
				this.counterService.onNight();
				this.chatService.timeSeparator("Tombée de la nuit");
				break;
			case 3:
			case 4:
			case 5:
				this.counterService.onNight();
				break;
			case 6:
				this.counterService.onDay();
				this.chatService.timeSeparator("Lever du jour");
				break;
			case 7:
				this.counterService.onDay();
				break;
			}
		},
		async getRound() {
			const round = await window.JSONFetch(`/rounds/${this.$route.params.id}`, "GET");
			return round.data[this.round][this.status];
		},
		async getState(status) {
			const state = await window.JSONFetch(`/states/${status ?? this.status}`, "GET");
			return state.data;
		}
	}
};
</script>
