import DayTimeHandler from "./TimeHandlers/DayTimeHandler.js";
import NightTimeHandler from "./TimeHandlers/NightTimeHandler.js";

export default class CounterCycleService {
	constructor() {
		this.dayHandler = new DayTimeHandler();
		this.nightHandler = new NightTimeHandler();
	}

	onNight() {
		this.state = "night";
		this.nightHandler.switchBackround();
		document.querySelector(".counter__icon").classList.remove("counter__icon-rotate");
	}

	onDay() {
		this.state = "day";
		this.dayHandler.switchBackround();
		document.querySelector(".counter__icon").classList.add("counter__icon-rotate");
	}
}
