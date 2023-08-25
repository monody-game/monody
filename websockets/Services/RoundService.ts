import fetch from "../Helpers/fetch.js";
import { fileURLToPath } from "node:url";
import { join, dirname } from "node:path";
import { readdirSync } from "node:fs";
import { error, log } from "../Logger.js";
import { Server } from "socket.io";
import { GameService } from "./GameService.js";

type StateIdentifier = number;

type Hook = {
	identifier: StateIdentifier;
	before?: (io: Server, channel: string) => Promise<boolean>;
	after?: (io: Server, channel: string) => Promise<boolean>;
};

type ApiState = {
	identifier: StateIdentifier;
	raw_name: string;
	duration: number;
};

type HookedState = Hook & ApiState;

type Round = HookedState[];

type RoundList = Round[];

export { Round, Hook, StateIdentifier, RoundList, HookedState };

export async function getRounds(gameId = ""): Promise<RoundList | []> {
	if (!(await GameService.exists(gameId))) {
		return [];
	}

	const response = await fetch(`${process.env.API_URL}/rounds/${gameId}`);

	if (!response.ok) {
		return [];
	}

	const __dirname = dirname(fileURLToPath(import.meta.url));
	const directory = join(__dirname, "../Hooks");
	const files = readdirSync(directory).filter((file) =>
		file.endsWith("Hook.js"),
	);
	const hooks = [];
	const hookedStates = [];
	const rounds = [];

	for (let file of files) {
		const imported = await import(directory + "/" + file);
		const hook: Hook = imported.default;
		hooks[hook.identifier] = hook;
		hookedStates.push(hook.identifier);
	}

	const apiRounds: { [key: string]: any } = response.json.data.rounds;

	try {
		for (const round in apiRounds) {
			const roundStates: Round = [];
			for (let state of apiRounds[round]) {
				if (hookedStates.includes(state.identifier)) {
					state = { ...state, ...hooks[state.identifier] };
				}
				roundStates.push(state);
			}
			rounds[parseInt(round)] = roundStates;
		}
	} catch (e) {
		error("Error retrieving rounds", e);
		log("Current rounds " + JSON.stringify(apiRounds));
	}

	return rounds;
}
