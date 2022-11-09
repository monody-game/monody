import fetch from "../Helpers/fetch.js";
import { fileURLToPath } from "node:url";
import { join, dirname } from "node:path";
import { readdirSync } from "node:fs";

export default async function getRounds(gameId = 0) {
	const apiRounds = await fetch(`https://web/api/rounds/${gameId}`, {
		"method": "GET"
	});

	const __dirname = dirname(fileURLToPath(import.meta.url));
	const directory = join(__dirname, "../Hooks");
	const files = readdirSync(directory).filter(file => file.endsWith("Hook.js"));
	const hooks = [];
	const hookedStates = [];
	const rounds = [];

	for (let file of files) {
		file = await import(directory + "/" + file);
		file = file.default;
		hooks[file.identifier] = file;
		hookedStates.push(file.identifier);
	}

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

	return rounds;
}
