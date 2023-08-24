import { Server } from "socket.io";
import fetch from "../Helpers/fetch.js";
import { gameId } from "../Helpers/Functions.js";

export default {
	identifier: 19,
	async before(io: Server, channel: string) {
		const id = gameId(channel);
		const body = {
			gameId: id,
		};

		await fetch(`${process.env.API_URL}/game/couple`, "POST", body);

		return false;
	},
};
