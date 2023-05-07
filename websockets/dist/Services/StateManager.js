import { client } from "../Redis/Connection.js";
import { getRounds } from "./RoundService.js";
import { ChatService } from "./ChatService.js";
import fetch from "../Helpers/fetch.js";
import { gameId } from "../Helpers/Functions.js";
import { error, log, warn } from "../Logger.js";
import { GameService } from "./GameService.js";
export class StateManager {
    io;
    emitter;
    constructor(io, emitter) {
        this.io = io;
        this.emitter = emitter;
    }
    /**
   * Set the current state of a game
   *
   * @param { Object } state
   * @param { Object } channel
   * @param { Boolean } isSkip
   * @returns self
   */
    async setState(state, channel, isSkip = false) {
        const id = gameId(channel);
        log(`Setting state of game ${id} to ${state.status} in round ${state.round || 0} for a duration of ${state.counterDuration}`);
        await client.set(`game:${id}:state`, JSON.stringify(state));
        this.io.to(channel).emit("game.state", channel, {
            status: state.status,
            counterDuration: state.counterDuration,
            startTimestamp: state.startTimestamp,
            round: state.round || 0
        });
        if (!isSkip) {
            const message = await fetch(`${process.env.API_URL}/state/${state.status}/message`);
            if (state.status > 1 && message.status !== 404) {
                ChatService.info(this.io, channel, message.json.data.state_message);
            }
        }
        return this;
    }
    async getState(id) {
        return JSON.parse(await client.get(`game:${id}:state`));
    }
    async nextState(channel, counterId) {
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
        }
        const rounds = roundList;
        const loopingRoundIndex = rounds.length - 2;
        let currentRound = state["round"] || 0;
        if (currentRound >= loopingRoundIndex) {
            currentRound = loopingRoundIndex;
        }
        const currentRoundObject = rounds[currentRound];
        if (!currentRoundObject)
            return;
        let stateIndex = currentRoundObject.findIndex(roundState => roundState.identifier === state["status"]) + 1;
        let currentState = typeof currentRoundObject[stateIndex] === "undefined" ? 0 : currentRoundObject[stateIndex].identifier;
        const isLast = stateIndex === currentRoundObject.length;
        halt = await this.handleAfter(isLast, currentRoundObject, stateIndex, channel);
        if (currentRound < loopingRoundIndex &&
            !currentRoundObject[stateIndex]) {
            // We are at the end of the current round
            currentRound++;
            const round = rounds[currentRound];
            currentState = round[0].identifier;
            stateIndex = 0;
        }
        else if (currentRound >= loopingRoundIndex && !currentRoundObject[stateIndex]) {
            // We are at the end of the looping round
            currentRound++;
            const round = rounds[currentRound];
            currentState = round[0].identifier;
            stateIndex = 0;
        }
        if (currentRound >= loopingRoundIndex) {
            currentRound = loopingRoundIndex;
        }
        const currentUsedRound = rounds[currentRound];
        const currentUsedState = currentUsedRound[stateIndex];
        let duration = currentUsedState.duration;
        halt = halt || await this.handleBefore(currentRoundObject, stateIndex, channel);
        if (halt) {
            const lastRound = rounds.at(-1);
            const endState = lastRound[0];
            currentState = endState.identifier;
            duration = endState.duration;
            this.emitter.emit("time.halt", id);
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
        const id = gameId(channel);
        const state = await this.getState(id);
        const rounds = await getRounds(id);
        if (!state)
            return 0;
        let currentRound = state["round"] || 0;
        const loopingRoundIndex = rounds.length - 2;
        if (currentRound >= loopingRoundIndex) {
            currentRound = loopingRoundIndex;
        }
        const currentRoundObject = rounds[currentRound];
        const stateIndex = currentRoundObject.findIndex(roundState => roundState.identifier === state["status"]) + 1;
        if (currentRound < loopingRoundIndex &&
            typeof currentRoundObject[stateIndex] === "undefined" &&
            typeof rounds[currentRound + 1] !== "undefined") {
            // If we are at the end of the current round
            const round = rounds[currentRound + 1];
            return round[0].duration;
        }
        else if (currentRound >= loopingRoundIndex &&
            typeof currentRoundObject[stateIndex] === "undefined") {
            // If we are at the end of the looping round
            const round = rounds[loopingRoundIndex];
            return round[0].duration;
        }
        else {
            // Otherwise return the next duration
            const state = currentRoundObject[stateIndex];
            return state.duration;
        }
    }
    async handleAfter(isLast, currentRoundObject, stateIndex, channel) {
        let halt = false;
        if (!currentRoundObject[stateIndex - 1] && !currentRoundObject.at(-1)) {
            return halt;
        }
        let hook = undefined;
        if (!isLast) {
            hook = currentRoundObject[stateIndex - 1];
        }
        else if (isLast) {
            hook = currentRoundObject.at(-1);
        }
        if (hook && hook.after) {
            await hook.after(this.io, channel);
        }
        return halt;
    }
    async handleBefore(currentRoundObject, stateIndex, channel) {
        let halt = false;
        if (!currentRoundObject[stateIndex]) {
            return halt;
        }
        const hook = currentRoundObject[stateIndex];
        if (hook.before) {
            halt = await hook.before(this.io, channel);
        }
        return halt;
    }
}
