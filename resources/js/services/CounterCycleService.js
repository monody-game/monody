import DayTimeHandler from "@/services/TimeHandlers/DayTimeHandler.js";
import NightTimeHandler from "@/services/TimeHandlers/NightTimeHandler.js";
import ChatService from "./ChatService";
import {useStore as useGameStore} from "@/stores/game.js"

export default class CounterCycleService {
  constructor() {
    this.dayHandler = new DayTimeHandler();
    this.nightHandler = new NightTimeHandler();
    this.chatService = new ChatService();
  }

  setState(state) {
    this.state = state;
    
  }

  getState() {
    return this.state;
  }

  onNight() {
    this.state = "night";
    this.nightHandler.switchBackround();
    this.switchChatState();
    this.chatService.timeSeparator("Tombée de la nuit");
    document.querySelector(".counter__icon").classList.remove("counter__icon-rotate");
  }

  onDay() {
    this.state = "day";
    this.dayHandler.switchBackround();
    this.chatService.timeSeparator("Lever du jour");
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
