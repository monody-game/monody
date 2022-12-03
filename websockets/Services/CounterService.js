import { StateManager } from "./StateManager.js";

export class CounterService {
	counterId = {};

	constructor(io) {
		this.io = io;
		this.manager = new StateManager(this.io);
	}

	async cycle(channel, socket) {
		const gameId = channel.split(".")[1];

		const counterId = setTimeout(async () => {
			await this.cycle(channel, socket);
		}, ((await this.manager.getNextStateDuration(channel)) + 1) * 1000);

		this.counterId[gameId] = counterId[Symbol.toPrimitive]();

		try {
			await this.manager.nextState(channel, this.counterId[Symbol.toPrimitive](), socket);
		} catch (e) {
			clearTimeout(this.counterId[gameId]);
			console.error(e);
		}
	}

	stop(gameId) {
		clearTimeout(this.counterId[gameId]);
	}
}
