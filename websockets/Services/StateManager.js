const { client } = require("../Redis/Connection");
const rounds = require("./RoundService");

module.exports = class StateManager {
	constructor(io) {
		this.io = io;
	}

	/**
   * Set the current state of a game
   *
   * @param { Object } state
   * @param { Object } channel
   * @returns self
   */
	async setState(state, channel) {
		await client.set(`game:${channel.split(".")[1]}:state`, JSON.stringify(state));

		this.io.to(channel).emit("game.state", channel, {
			state: state.status,
			counterDuration: state.counterDuration,
			startTimestamp: state.startTimestamp
		});

		return this;
	}

	/**
   * Get the current state of a game
   *
   * @param { String } gameId
   * @returns { Promise<Object> }
   */
	async getState(gameId) {
		return JSON.parse(await client.get(`game:${gameId}:state`));
	}

	/**
   * Switch the state to the next
   *
   * @param { object } channel
   * @param { number } counterId
   * @returns {Promise<{duration: number, state: number}>} context The next round context
   */
	async nextState(channel, counterId) {
		const state = (await this.getState(channel.split(".")[1]));
		if (!state) {
			clearTimeout(counterId);
			return;
		}

		let currentState = state["status"];
		let currentRound = state["round"] || 0;
		const loopingIndex = rounds.length - 1;

		let duration;

		if (
			currentRound === loopingIndex &&
			rounds[loopingIndex].indexOf(rounds[loopingIndex].filter(loopRoundState => loopRoundState.identifier === currentState)[0]) === rounds[loopingIndex].length - 1
		) {
			// If we're at the end of the looping round

			duration = rounds[loopingIndex][0].duration;
			console.log(duration);
			currentState = rounds[loopingIndex][0].identifier;
		} else if (!rounds[currentRound][currentState + 1] && rounds[currentRound].length === currentState) {
			// If it's the end of the currrent round

			currentRound++;
			duration = rounds[currentRound][0].duration;
			console.log(duration);
			currentState = rounds[currentRound][0].identifier;
		} else {
			// Else :

			currentState++;
			duration = rounds[currentRound][currentState].duration;
			console.log(duration);
		}

		await this.setState({
			status: currentState,
			startTimestamp: Date.now(),
			counterDuration: duration,
			counterId: counterId,
			round: currentRound
		}, channel);

		return {
			duration,
			state: currentState
		};
	}

	async getNextStateDuration(channel) {
		const state = await this.getState(channel.split(".")[1]);
		if (!state) return;

		const currentState = state["status"];
		const currentRound = state["round"] || 0;
		const loopingIndex = rounds.length - 1;

		if (
			currentRound === loopingIndex &&
			rounds[loopingIndex].indexOf(rounds[loopingIndex].filter(loopRoundState => loopRoundState.identifier === currentState)[0]) === rounds[loopingIndex].length - 1
		) {
			return rounds[loopingIndex][0].duration;
		} else if (!rounds[currentRound][currentState + 1] && rounds[currentRound].length - 1 === currentState) {
			return rounds[currentRound][0].duration;
		} else {
			return rounds[currentRound][currentState + 1].duration;
		}
	}
};
