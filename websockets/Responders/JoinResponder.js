import { BaseResponder } from "./BaseResponder.js";
import { StateManager } from "../Services/StateManager.js";
import { gameId } from "../Helpers/Functions.js";

export default class JoinResponder extends BaseResponder {
	constructor() {
		super();
		this.respondTo = [
			/^subscribe$/
		];
	}

	async emit(socket, data) {
		const id = gameId(data.channel);
		const manager = new StateManager();

		if (!id) return;

		const game = await manager.getState(id);

		if (game && game.is_started) {
			socket.emit("game.state", data.channel, {
				state: game.status,
				counterDuration: game.counterDuration,
				startTimestamp: game.startTimestamp
			});
		}
	}
}
