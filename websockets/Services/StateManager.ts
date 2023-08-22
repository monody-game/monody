import { client } from "../Redis/Connection.js";
import {
	getRounds,
	Hook,
	HookedState,
	Round,
	RoundList,
	StateIdentifier,
} from "./RoundService.js";
import { gameId } from "../Helpers/Functions.js";
import { error, info, warn } from "../Logger.js";
import { GameService } from "./GameService.js";
import { Server } from "socket.io";
import { EventEmitter } from "node:events";

type State = {
	status: number;
	round: number | null;
	counterDuration: number;
	startTimestamp: number;
	counterId?: number;
	skipped?: boolean;
};

export { State };

export class StateManager {
	private readonly io: Server;
	private emitter: EventEmitter;
	private LOOP_ROUND = 2;
	private END_ROUND = 999999;

	constructor(io: Server, emitter: EventEmitter) {
		this.io = io;
		this.emitter = emitter;
	}

	async setState(state: State, channel: string, isSkip = false) {
		const id = gameId(channel);

		info(
			`Setting state of game ${id} to ${state.status} in round ${
				state.round || 0
			} for a duration of ${state.counterDuration}`,
		);
		await client.set(`game:${id}:state`, JSON.stringify(state));

		this.io.to(channel).emit("game.state", channel, {
			status: state.status,
			counterDuration: state.counterDuration,
			startTimestamp: state.startTimestamp,
			round: state.round || 0,
			skipped: isSkip,
		});

		return this;
	}

	async getState(id: string): Promise<State> {
		return JSON.parse((await client.get(`game:${id}:state`)) as string);
	}

	async nextState(channel: string, counterId: number) {
		const id = gameId(channel);
		const game = await GameService.getGame(id);

		if (game.ended && game.ended === true) {
			warn("Counter tried to retrieve next state in an ended game");
			return;
		}

		const state = await this.getState(id);
		let halt = false;

		if (!state) {
			clearTimeout(counterId);
			return;
		}

		const roundList = await getRounds(id);

		if (roundList.length === 0) {
			error(`Round list is empty for game ${id}`);
			return;
		}

		let rounds = roundList as RoundList;

		let currentRound = state["round"] || 0;
		let roundIndex = state["round"] || 0;

		if (currentRound >= this.LOOP_ROUND) {
			currentRound = this.LOOP_ROUND;
		}

		let currentRoundObject = rounds[currentRound];
		if (!currentRoundObject) return;

		let stateIndex =
			currentRoundObject.findIndex(
				(roundState) => roundState.identifier === state["status"],
			) + 1;
		let currentState: StateIdentifier =
			typeof currentRoundObject[stateIndex] === "undefined"
				? 0
				: (currentRoundObject[stateIndex] as Hook).identifier;
		let isLast = stateIndex === currentRoundObject.length;

		halt = await this.handleAfter(
			isLast,
			currentRoundObject,
			stateIndex,
			channel,
		);

		if (currentState === 6 || state["status"] === 7) {
			console.log("should show this")
			rounds = (await getRounds(id)) as RoundList;

			if (rounds.length === 0) {
				error(`Round list is empty for game ${id}`);
				return;
			}

			currentRoundObject = rounds[currentRound];

			if (!currentRoundObject) return;

			stateIndex =
				currentRoundObject.findIndex(
					(roundState) => roundState.identifier === state["status"],
				) + 1;
			currentState =
				typeof currentRoundObject[stateIndex] === "undefined"
					? 0
					: (currentRoundObject[stateIndex] as Hook).identifier;
		}

		if (currentRound < this.LOOP_ROUND && !currentRoundObject[stateIndex]) {
			// We are at the end of the current round
			currentRound++;
			roundIndex++;
			const round = rounds[currentRound] as Hook[];
			currentState = (round[0] as Hook).identifier;
			stateIndex = 0;
		} else if (
			currentRound >= this.LOOP_ROUND &&
			!currentRoundObject[stateIndex]
		) {
			// We are at the end of the looping round
			currentRound++;
			roundIndex++;
			const round = rounds[this.LOOP_ROUND] as Hook[];
			currentState = (round[0] as Hook).identifier;
			stateIndex = 0;
		}

		if (currentRound >= this.LOOP_ROUND) {
			currentRound = this.LOOP_ROUND;
		}

		const currentUsedRound = rounds[currentRound] as Round;
		const currentUsedState = currentUsedRound[stateIndex] as HookedState;
		let duration = currentUsedState.duration;

		halt =
			halt ||
			(await this.handleBefore(currentRoundObject, stateIndex, channel));

		if (halt) {
			const lastRound = rounds[this.END_ROUND] as Round;
			const endState = lastRound[0] as HookedState;
			currentState = endState.identifier;
			duration = endState.duration;

			this.emitter.emit("time.halt", id);
		}

		await this.setState(
			{
				status: currentState,
				startTimestamp: Date.now(),
				counterDuration: duration,
				counterId: counterId,
				round: roundIndex,
			},
			channel,
		);
	}

	async getNextStateDuration(channel: string): Promise<number> {
		const id = gameId(channel);
		const state = await this.getState(id);
		const rounds = (await getRounds(id)) as RoundList;
		if (!state) return 0;

		let currentRound = state["round"] || 0;

		if (currentRound >= this.LOOP_ROUND) {
			currentRound = this.LOOP_ROUND;
		}

		const currentRoundObject = rounds[currentRound] as Round;
		const stateIndex =
			currentRoundObject.findIndex(
				(roundState) => roundState.identifier === state["status"],
			) + 1;

		if (
			currentRound < this.LOOP_ROUND &&
			typeof currentRoundObject[stateIndex] === "undefined" &&
			typeof rounds[currentRound + 1] !== "undefined"
		) {
			// If we are at the end of the current round
			const round = rounds[currentRound + 1] as Round;
			return (round[0] as HookedState).duration;
		} else if (
			currentRound >= this.LOOP_ROUND &&
			typeof currentRoundObject[stateIndex] === "undefined"
		) {
			// If we are at the end of the looping round
			const round = rounds[this.LOOP_ROUND] as Round;
			return (round[0] as HookedState).duration;
		} else {
			// Otherwise return the next duration
			const state = currentRoundObject[stateIndex] as HookedState;
			return state.duration;
		}
	}

	private async handleAfter(
		isLast: boolean,
		currentRoundObject: Round,
		stateIndex: number,
		channel: string,
	) {
		let halt = false;

		if (!currentRoundObject[stateIndex - 1] && !currentRoundObject.at(-1)) {
			return halt;
		}

		let hook = undefined;

		if (!isLast) {
			hook = currentRoundObject[stateIndex - 1] as Hook;
		} else if (isLast) {
			hook = currentRoundObject.at(-1) as Hook;
		}

		if (hook && hook.after) {
			halt = await hook.after(this.io, channel);
		}

		return halt;
	}

	private async handleBefore(
		currentRoundObject: Round,
		stateIndex: number,
		channel: string,
	) {
		let halt = false;

		if (!currentRoundObject[stateIndex]) {
			return halt;
		}

		const hook = currentRoundObject[stateIndex] as Hook;

		if (hook.before) {
			halt = await hook.before(this.io, channel);
		}

		return halt;
	}
}
