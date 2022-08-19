import { readdirSync } from "node:fs";
import { join, dirname } from "node:path";
import { fileURLToPath } from "node:url";

const __dirname = dirname(fileURLToPath(import.meta.url));
const rounds = [];
const directory = join(__dirname, "../Rounds");
const files = readdirSync(directory).filter(file => file.endsWith("Round.js"));

for (let file of files) {
	file = await import(directory + "/" + file);
	const position = file.default.splice(0, 1)[0];
	rounds[position] = file.default;
}

export default rounds;
