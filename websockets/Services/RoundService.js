import fetch from "../Helpers/fetch.js";
import { readdirSync } from "node:fs";
import { join, dirname } from "node:path";
import { fileURLToPath } from "node:url";

export default async function getRounds(gameId) {
	const apiRounds = await fetch(`https://web/api/rounds/${gameId}`, {
		"method": "GET"
	});
	const __dirname = dirname(fileURLToPath(import.meta.url));
	const directory = join(__dirname, "../States");
	const files = readdirSync(directory).filter(file => file.endsWith("State.js"));
	const rounds = [];
	const imported = [];

	for (let file of files) {
		file = await import(directory + "/" + file);
		file = file.default;
		imported[file.identifier] = file;
	}

	for (const round of apiRounds.json) {
		const currentState = [];
		for (const state of round) {
			currentState.push(imported[state]);
			if (round.indexOf(state) === round.length - 1) {
				rounds.push(currentState);
			}
		}
	}
	return rounds;
}
