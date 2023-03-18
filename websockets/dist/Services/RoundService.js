import fetch from "../Helpers/fetch.js";
import { fileURLToPath } from "node:url";
import { join, dirname } from "node:path";
import { readdirSync } from "node:fs";
import { GameService } from "./GameService.js";
import { error, log } from "../Logger.js";
export async function getRounds(gameId = "") {
    if (!await GameService.exists(gameId)) {
        return [];
    }
    const apiRounds = await fetch(`${process.env.API_URL}/rounds/${gameId}`);
    const __dirname = dirname(fileURLToPath(import.meta.url));
    const directory = join(__dirname, "../Hooks");
    const files = readdirSync(directory).filter(file => file.endsWith("Hook.js"));
    const hooks = [];
    const hookedStates = [];
    const rounds = [];
    for (let file of files) {
        const imported = await import(directory + "/" + file);
        const hook = imported.default;
        hooks[hook.identifier] = hook;
        hookedStates.push(hook.identifier);
    }
    try {
        for (const round of apiRounds.json) {
            const roundStates = [];
            for (let state of round) {
                if (hookedStates.includes(state.identifier)) {
                    state = { ...state, ...hooks[state.identifier] };
                }
                roundStates.push(state);
            }
            rounds.push(roundStates);
        }
    }
    catch (e) {
        error("Error retrieving rounds", e);
        log("Current rounds " + JSON.stringify(apiRounds));
    }
    return rounds;
}
