import { client } from "../Redis/Connection.js";
const rounds = (await import("./RoundService.js")).default;

export class StateManager {
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
		const members = await this.getMembers(channel);
		const isLast = stateIndex === -1;
		const currentRoundObject = rounds[currentRound];

		if (
			!isLast &&
			typeof currentRoundObject[stateIndex - 1] !== "undefined" &&
			typeof currentRoundObject[stateIndex - 1].after === "function"
		) {
			await currentRoundObject[stateIndex - 1].after(this.io, channel, members);
		} else if (isLast) {
			console.log("should be the last state of the round");
			await currentRoundObject[currentRoundObject.length - 1].after(this.io, channel, members);
		}

		if (
			currentRound !== loopingRoundIndex &&
			typeof currentRoundObject[stateIndex] === "undefined" &&
			typeof rounds[currentRound + 1] !== "undefined"
		) {
			currentRound++;
			currentState = rounds[currentRound][0].identifier;
			stateIndex = 0;
		} else if (currentRound === loopingRoundIndex && stateIndex === currentRoundObject.length - 1) {
			currentRound = loopingRoundIndex;
			currentState = rounds[loopingRoundIndex][0].identifier;
			stateIndex = 0;
		}

		const duration = rounds[currentRound][stateIndex].duration;

		if (typeof rounds[currentRound][stateIndex] !== "undefined" && typeof rounds[currentRound][stateIndex].before === "function") {
			await rounds[currentRound][stateIndex].before(this.io, channel, members);
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

	async getMembers(channel) {
		const members = JSON.parse(await client.get(`game:${channel.split(".")[1]}:members`));
		if (!members) return [];
		return members;
	}
}
