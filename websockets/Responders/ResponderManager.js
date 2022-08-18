import { readdirSync } from "node:fs";
import { dirname } from "node:path";
import { fileURLToPath } from "node:url";

const __dirname = dirname(fileURLToPath(import.meta.url));

export class ResponderManager {
	static getAll() {
		return readdirSync(__dirname)
			.filter(file => file !== "BaseResponder.js" && file.endsWith("Responder.js"))
			.map(async file => await import(`${__dirname}/${file}`));
	}

	static findResponder(event, responders) {
		return responders.find(responder => responder.canRespond(event));
	}
}
