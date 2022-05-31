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

		let currentState = state["status"] + 1;
		let currentRound = state["round"] || 0;
		let stateIndex = rounds[currentRound].indexOf(rounds[currentRound].find(roundState => roundState.identifier === currentState));
		const loopingRoundIndex = rounds.length - 1;

		if (currentRound !== loopingRoundIndex && typeof rounds[currentRound][stateIndex] === "undefined" && typeof rounds[currentRound + 1] !== "undefined") {
			currentRound++;
			currentState = rounds[currentRound][0].identifier;
			stateIndex = 0;
		} else if (currentRound === loopingRoundIndex && stateIndex === rounds[currentRound].length - 1) {
			currentRound = loopingRoundIndex;
			currentState = rounds[loopingRoundIndex][0].identifier;
			stateIndex = 0;
		}

		const duration = rounds[currentRound][stateIndex].duration;

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

		const currentState = state["status"] + 1;
		const currentRound = state["round"] || 0;
		const stateIndex = rounds[currentRound].indexOf(rounds[currentRound].find(roundState => roundState.identifier === currentState));
		const loopingRoundIndex = rounds.length - 1;

		if (
			(currentRound !== loopingRoundIndex && typeof rounds[currentRound][stateIndex] === "undefined" && currentRound + 1 === loopingRoundIndex) ||
			(currentRound === loopingRoundIndex && stateIndex === rounds[currentRound].length - 1)
		) {
			return rounds[loopingRoundIndex][0].duration;
		} else if (
			currentRound !== loopingRoundIndex && typeof rounds[currentRound][stateIndex] === "undefined"
		) {
			return rounds[currentRound + 1][0].duration;
		} else {
			return rounds[currentRound][stateIndex].duration;
		}
	}
};
