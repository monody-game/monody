import DayTimeHandler from "./TimeHandlers/DayTimeHandler.js";
import NightTimeHandler from "./TimeHandlers/NightTimeHandler.js";
import ChatService from "./ChatService";

export default class CounterCycleService {
	constructor() {
		this.dayHandler = new DayTimeHandler();
		this.nightHandler = new NightTimeHandler();
		this.chatService = new ChatService();
	}

	onNight() {
		this.state = "night";
		this.nightHandler.switchBackround();
		this.switchChatState();
		document.querySelector(".counter__icon").classList.remove("counter__icon-rotate");
	}

	onDay() {
		this.state = "day";
		this.dayHandler.switchBackround();
		this.switchChatState();
		document.querySelector(".counter__icon").classList.add("counter__icon-rotate");
	}

	switchChatState() {
		this.chatService.lock();

		if (this.state === "day") {
			this.chatService.unlock();
		}
	}
}
