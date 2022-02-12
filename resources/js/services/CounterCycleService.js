import DayTimeHandler from "@/services/TimeHandlers/DayTimeHandler.js";
import NightTimeHandler from "@/services/TimeHandlers/NightTimeHandler.js";
import ChatService from "./ChatService";
import { useStore as useGameStore } from "@/stores/game.js"
import { useStore as useUserStore } from "@/stores/user.js"

export default class CounterCycleService {

  DEFAULT_DAY_TIME = 30;

  DEFAULT_NIGHT_TIME = 30;

  constructor () {
    this.dayHandler = new DayTimeHandler();
    this.nightHandler = new NightTimeHandler();
    this.chatService = new ChatService();
    this.actual = "wait";
    this.state = "wait";
  }

  switch () {
    if (this.actual === "day") {
      this.actual = "night";
      this.onNight();
    } else {
      this.actual = "day";
      this.onDay();
    }
  }

  getState () {
    return this.state;
  }

  getTimeCounter () {
    if (this.actual === "day") {
      return this.DEFAULT_DAY_TIME;
    } else if (this.actual === "night") {
      return this.DEFAULT_NIGHT_TIME;
    } else if (this.actual === "wait") {
      return 0;
    }
  }

  onNight () {
    this.state = "night";
    this.nightHandler.switchBackround();
    this.switchChatState();
    this.chatService.timeSeparator("Tombée de la nuit");
    document.querySelector(".counter__icon").classList.remove("counter__icon-rotate");
  }

  onDay () {
    this.state = "day";
    this.dayHandler.switchBackround();
    this.chatService.timeSeparator("Lever du jour");
    this.switchChatState();
    document.querySelector(".counter__icon").classList.add("counter__icon-rotate");
  }

  switchChatState () {
    if (this.actual === "day") {
      this.chatService.unlock();
    } else if (this.actual === "night") {
      if (!this.isWerewolf()) {
        this.chatService.lock();
      }
    }
  }

  isWerewolf () {
    const id = useUserStore().id;
    const player = useGameStore().getPlayerByID(id);

    return player.role.group === "werewolf";
  }
}
