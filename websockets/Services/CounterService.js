const StateManager = require("./StateManager");

module.exports = class CounterService {
	constructor(io) {
		this.io = io;
	}

	async cycle(channel) {
		const manager = new StateManager(this.io);

		this.counterId = setTimeout(async () => {
			await this.cycle(channel);
		}, (await manager.getNextStateDuration(channel) + 1) * 1000);

		try {
			await manager.nextState(channel, this.counterId[Symbol.toPrimitive]());
		} catch (e) {
			clearTimeout(this.counterId);
			console.error(e);
		}
	}
};
