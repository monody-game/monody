const BaseResponder = require("./BaseResponder");
const StateManager = new (require("../Services/StateManager"))();
const states = require("../Constants/GameStates");

module.exports = class JoinResponder extends BaseResponder {
	constructor() {
		super();
		this.respondTo = [
			/^subscribe$/
		];
	}

	async emit(socket, data) {
		const id = data.channel.split(".")[1];
		const game = await StateManager.getState(id);

		if (game && game.is_started) {
			socket.emit("game.state", data.channel, {
				state: Object.keys(states)[game.status],
				counterDuration: game.counterDuration,
				startTimestamp: game.startTimestamp
			});
		}
	}
};
