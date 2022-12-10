import { StateManager } from "./StateManager.js";
import { gameId } from "../Helpers/Functions.js";

export class CounterService {
	counterId = {};

	constructor(io) {
		this.io = io;
		this.manager = new StateManager(this.io);
	}

	async cycle(channel, socket) {
		const id = gameId(channel);
		let halt = false;

		const counterId = setTimeout(async () => {
			await this.cycle(channel, socket);
		}, ((await this.manager.getNextStateDuration(channel)) + 1) * 1000);

		this.counterId[id] = counterId[Symbol.toPrimitive]();

		try {
			halt = await this.manager.nextState(channel, this.counterId[id], socket);
		} catch (e) {
			clearTimeout(this.counterId[id]);
			console.error(e);
		}

		if (halt) {
			this.stop(id);
		}
	}

	stop(id) {
		clearTimeout(this.counterId[id]);
	}
}
