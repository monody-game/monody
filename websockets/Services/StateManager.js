import { client } from "../Redis/Connection.js";
import rounds from "./RoundService.js";

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

		let currentRound = state["round"] || 0;
		const currentRoundObject = rounds[currentRound];
		let stateIndex = currentRoundObject.indexOf(currentRoundObject.find(roundState => roundState.identifier === state["status"])) + 1;
		const loopingRoundIndex = rounds.length - 1;
		let currentState = currentRoundObject[stateIndex].identifier;

		const members = await this.getMembers(channel);
		const isLast = stateIndex === currentRoundObject.length;

		if (
			!isLast &&
			typeof currentRoundObject[stateIndex - 1] !== "undefined" &&
			typeof currentRoundObject[stateIndex - 1].after === "function"
		) {
			await currentRoundObject[stateIndex - 1].after(this.io, channel, members);
		} else if (isLast) {
			await currentRoundObject[currentRoundObject.length - 1].after(this.io, channel, members);
		}

		if (
			currentRound !== loopingRoundIndex &&
			typeof currentRoundObject[stateIndex] === "undefined" &&
			typeof rounds[currentRound + 1] !== "undefined"
		) {
			// We are at the end of the current round
			currentRound++;
			currentState = currentRoundObject[0].identifier;
			stateIndex = 0;
		} else if (currentRound === loopingRoundIndex && stateIndex === currentRoundObject.length - 1) {
			// We are at the end of the looping round
			currentRound = loopingRoundIndex;
			currentState = rounds[loopingRoundIndex][0].identifier;
			stateIndex = 0;
		}

		const duration = currentRoundObject[stateIndex].duration;

		if (typeof currentRoundObject[stateIndex] !== "undefined" && typeof currentRoundObject[stateIndex].before === "function") {
			await currentRoundObject[stateIndex].before(this.io, channel, members);
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

		const currentRound = state["round"] || 0;
		const currentRoundObject = rounds[currentRound];
		const stateIndex = currentRoundObject.indexOf(currentRoundObject.find(roundState => roundState.identifier === state["status"])) + 1;
		const loopingRoundIndex = rounds.length - 1;

		if (
			(currentRound !== loopingRoundIndex && typeof currentRoundObject[stateIndex] === "undefined" && currentRound + 1 === loopingRoundIndex) ||
			(currentRound === loopingRoundIndex && stateIndex === currentRoundObject.length - 1)
		) {
			// If we are at the end of the looping round
			return rounds[loopingRoundIndex][0].duration;
		} else if (
			currentRound !== loopingRoundIndex && typeof currentRoundObject[stateIndex] === "undefined"
		) {
			// If we are at the end of the current round
			return rounds[currentRound + 1][0].duration;
		} else {
			// Otherwise return the next duration
			return currentRoundObject[stateIndex].duration;
		}
	}

	async getMembers(channel) {
		const members = JSON.parse(await client.get(`game:${channel.split(".")[1]}:members`));
		if (!members) return [];
		return members;
	}
}
