import { InteractionService } from "../Services/InteractionService.js";
import fetch from "../Helpers/fetch.js";
import { gameId } from "../Helpers/Functions.js";
import {Server} from "socket.io";

const baseURL = `${process.env.API_URL}/game`;

export default {
	identifier: 3,
	async before(io: Server, channel: string) {
		await InteractionService.openInteraction(io, channel, "werewolves");

		await fetch(`${baseURL}/chat/lock/false`, "POST", {
			gameId: gameId(channel),
			team: "2"
		});

		return false;
	},
	async after(io: Server, channel: string) {
		await InteractionService.closeInteraction(io, channel, "werewolves");

		await fetch(`${baseURL}/chat/lock/true`, "POST", {
			gameId: gameId(channel),
			team: "2"
		});

		return false;
	},
};
