import { StateManager } from "./StateManager.js";

export class CounterService {
	constructor(io) {
		this.io = io;
		this.manager = new StateManager(this.io);
	}

	async cycle(channel, socket) {
		{this.counterId = setTimeout(async () => {
			await this.cycle(channel, socket);
		}, ((await this.manager.getNextStateDuration(channel)) + 1) * 1000);}

		try {
			await this.manager.nextState(channel, this.counterId[Symbol.toPrimitive](), socket);
		} catch (e) {
			clearTimeout(this.counterId);
			console.error(e);
		}
	}
}
