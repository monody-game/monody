import fetch from "../Helpers/fetch.js";
import { gameId } from "../Helpers/Functions.js";
import {Server} from "socket.io";

export default {
	identifier: 2,
	async before(io: Server, channel: string) {
		await fetch(`${process.env.API_URL}/game/chat/lock`, "POST", { gameId: gameId(channel) });
		return false;
	}
};
