import { StateManager } from "./StateManager.js";
import { gameId } from "../Helpers/Functions.js";
import { error, log } from "../Logger.js";

export class CounterService {
	counterId = {};

	constructor(io, emitter) {
		this.io = io;
		this.emitter = emitter;
		this.manager = new StateManager(this.io);
	}

	async cycle(channel, socket, duration = null) {
		this.clearListeners();

		const id = gameId(channel);
		let halt = false;

		const counterId = setTimeout(async () => {
			await this.cycle(channel, socket);
		}, duration ?? ((await this.manager.getNextStateDuration(channel)) + 2) * 1000);

		this.emitter.on("time.skip", async (data) => {
			clearTimeout(this.counterId[data.gameId]);

			const state = await this.manager.getState(data.gameId);
			log(`Skipping time in game ${data.gameId}, in state ${state.status} to time ${data.to}`);

			this.manager.setState({
				status: state.status,
				startTimestamp: Date.now(),
				counterDuration: data.to,
				counterId: counterId,
				round: state.round
			}, `presence-game.${data.gameId}`, true);

			setTimeout(async () => {
				await this.cycle(channel, socket);
			}, (data.to + 2) * 1000);
		});

		this.emitter.on("time.halt", async (gameId) => {
			clearTimeout(this.counterId[gameId]);
		});

		this.counterId[id] = counterId[Symbol.toPrimitive]();

		try {
			halt = await this.manager.nextState(channel, this.counterId[id], socket);
		} catch (e) {
			clearTimeout(this.counterId[id]);
			error(`Error happenned during counter cycle in game ${id}:`);
			error(e);
		}

		if (halt) {
			this.stop(id);
		}
	}

	stop(id) {
		clearTimeout(this.counterId[id]);
	}

	clearListeners() {
		const listeners = this.emitter.listeners("time.skip");

		for (const listener of listeners) {
			this.emitter.off("time.skip", listener);
		}
	}
}
