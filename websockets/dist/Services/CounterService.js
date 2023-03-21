import { StateManager } from "./StateManager.js";
import { gameId } from "../Helpers/Functions.js";
import { error, log, warn } from "../Logger.js";
import { GameService } from "./GameService.js";
export class CounterService {
    counterId = {};
    io;
    emitter;
    manager;
    constructor(io, emitter) {
        this.io = io;
        this.emitter = emitter;
        this.manager = new StateManager(this.io, emitter);
    }
    async cycle(channel, socket, duration = null) {
        this.clearListeners();
        const id = gameId(channel);
        const game = await GameService.getGame(id);
        if (!game)
            return;
        if (game.ended && game.ended === true) {
            warn("Counter tried to change state in an ended game");
            return;
        }
        const counterId = setTimeout(async () => {
            await this.cycle(channel, socket);
        }, duration ?? ((await this.manager.getNextStateDuration(channel)) + 2) * 1000);
        this.emitter.on("time.skip", async (data) => {
            const state = await this.manager.getState(data.gameId);
            if (state.skipped)
                return;
            clearTimeout(this.counterId[data.gameId]);
            log(`Skipping time in game ${data.gameId}, in state ${state.status} to time ${data.to}`);
            await this.manager.setState({
                status: state.status,
                startTimestamp: Date.now(),
                counterDuration: data.to,
                counterId: this.counterId[data.gameId],
                round: state.round,
                skipped: true
            }, `presence-game.${data.gameId}`, true);
            setTimeout(async () => {
                await this.cycle(channel, socket);
            }, (data.to + 2) * 1000);
        });
        this.emitter.on("time.halt", async (gameId) => {
            clearTimeout(this.counterId[gameId]);
        });
        const timeoutId = counterId[Symbol.toPrimitive]();
        this.counterId[id] = timeoutId;
        try {
            await this.manager.nextState(channel, timeoutId);
        }
        catch (e) {
            clearTimeout(this.counterId[id]);
            error(`Error happenned during counter cycle in game ${id}:`);
            error(e);
        }
    }
    clearListeners() {
        this.emitter.removeAllListeners("time.halt");
        this.emitter.removeAllListeners("time.skip");
    }
}
