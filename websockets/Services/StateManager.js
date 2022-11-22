import { client } from "../Redis/Connection.js";
import getRounds from "./RoundService.js";
import { ChatService } from "./ChatService.js";
import fetch from "../Helpers/fetch.js";

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
		const gameId = channel.split(".")[1];

		console.info(`Setting state of game ${gameId} to ${state.status} in round ${state.round} for a duration of ${state.counterDuration}`);
		await client.set(`game:${gameId}:state`, JSON.stringify(state));
		const message = await fetch(`https://web/api/state/${state.status}/message`);

		this.io.to(channel).emit("game.state", channel, {
			state: state.status,
			counterDuration: state.counterDuration,
			startTimestamp: state.startTimestamp,
			round: state.round
		});

		if (state.status > 1 && message.status !== 404) {
			ChatService.info(this.io, channel, message.json.message);
		}

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
   */
	async nextState(channel, counterId) {
		const gameId = channel.split(".")[1];
		const state = await this.getState(gameId);

		if (!state) {
			clearTimeout(counterId);
			return;
		}

		const rounds = await getRounds(gameId);
		const loopingRoundIndex = rounds.length - 1;

		let currentRound = state["round"] || 0;

		if (currentRound >= loopingRoundIndex) {
			currentRound = loopingRoundIndex;
		}

		const currentRoundObject = rounds[currentRound];
		let stateIndex = currentRoundObject.indexOf(currentRoundObject.find(roundState => roundState.identifier === state["status"])) + 1;
		let currentState = typeof currentRoundObject[stateIndex] === "undefined" ? {} : currentRoundObject[stateIndex].identifier;
		const isLast = stateIndex === currentRoundObject.length;

		if (
			!isLast &&
			typeof currentRoundObject[stateIndex - 1] !== "undefined" &&
			typeof currentRoundObject[stateIndex - 1].after === "function"
		) {
			await currentRoundObject[stateIndex - 1].after(this.io, channel);
		} else if (
			isLast &&
			typeof currentRoundObject[currentRoundObject.length - 1].after === "function"
		) {
			await currentRoundObject[currentRoundObject.length - 1].after(this.io, channel);
		}

		if (
			currentRound < loopingRoundIndex &&
			typeof currentRoundObject[stateIndex] === "undefined" &&
			typeof rounds[currentRound + 1] !== "undefined"
		) {
			// We are at the end of the current round
			currentRound++;
			currentState = rounds[currentRound][0].identifier;
			stateIndex = 0;
		} else if (currentRound >= loopingRoundIndex && typeof currentRoundObject[stateIndex] === "undefined") {
			// We are at the end of the looping round
			currentRound++;
			currentState = rounds[loopingRoundIndex][0].identifier;
			stateIndex = 0;
		}

		if (currentRound >= loopingRoundIndex) {
			currentRound = loopingRoundIndex;
		}

		const duration = rounds[currentRound][stateIndex].duration;

		if (typeof currentRoundObject[stateIndex] !== "undefined" && typeof currentRoundObject[stateIndex].before === "function") {
			await currentRoundObject[stateIndex].before(this.io, channel);
		}

		await this.setState({
			status: currentState,
			startTimestamp: Date.now(),
			counterDuration: duration,
			counterId: counterId,
			round: currentRound
		}, channel);
	}

	async getNextStateDuration(channel) {
		const gameId = channel.split(".")[1];
		const state = await this.getState(gameId);
		const rounds = await getRounds(gameId);
		if (!state) return;

		let currentRound = state["round"] || 0;
		const loopingRoundIndex = rounds.length - 1;

		if (currentRound > loopingRoundIndex) {
			currentRound = loopingRoundIndex;
		}

		const currentRoundObject = rounds[currentRound];
		const stateIndex = currentRoundObject.indexOf(currentRoundObject.find(roundState => roundState.identifier === state["status"])) + 1;

		if (
			currentRound < loopingRoundIndex &&
			typeof currentRoundObject[stateIndex] === "undefined" &&
			typeof rounds[currentRound + 1] !== "undefined"
		) {
			// If we are at the end of the current round
			return rounds[currentRound + 1][0].duration;
		} else if (
			currentRound >= loopingRoundIndex &&
			typeof currentRoundObject[stateIndex] === "undefined"
		) {
			// If we are at the end of the looping round
			return rounds[loopingRoundIndex][0].duration;
		} else {
			// Otherwise return the next duration
			return currentRoundObject[stateIndex].duration;
		}
	}
}
