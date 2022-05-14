import DayTimeHandler from "@/services/TimeHandlers/DayTimeHandler.js";
import NightTimeHandler from "@/services/TimeHandlers/NightTimeHandler.js";
import ChatService from "./ChatService";
import { useStore as useGameStore } from "@/stores/game.js"

export default class CounterCycleService {
  constructor() {
    this.dayHandler = new DayTimeHandler();
    this.nightHandler = new NightTimeHandler();
    this.chatService = new ChatService();
  }

  onNight() {
    this.state = "night"
    this.nightHandler.switchBackround();
    this.switchChatState();
    document.querySelector(".counter__icon").classList.remove("counter__icon-rotate");
  }

  onDay() {
    this.state = "day"
    this.dayHandler.switchBackround();
    this.switchChatState();
    document.querySelector(".counter__icon").classList.add("counter__icon-rotate");
  }

  switchChatState() {
    if (this.state === "day") {
      this.chatService.unlock();
    } else if (this.state === "night") {
      if (!useGameStore().isWerewolf) {
        this.chatService.lock();
      }
    }
  }
}
