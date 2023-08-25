import { InteractionService } from "../Services/InteractionService.js";
import { Server } from "socket.io";
import fetch from "../Helpers/fetch.js";
import { gameId } from "../Helpers/Functions.js";

const baseUrl = `${process.env.API_URL}/game`;
const body = (channel: string) => {
	return { gameId: gameId(channel) };
};

export default {
	identifier: 17,
	async before(io: Server, channel: string) {
		await fetch(`${baseUrl}/chat/lock/false`, "POST", body(channel));

		await InteractionService.openInteraction(io, channel, "hunter");

		return false;
	},
	async after(io: Server, channel: string) {
		await InteractionService.closeInteraction(io, channel, "hunter");

		await fetch(`${baseUrl}/message/deaths`, "POST", body(channel));
		await fetch(`${baseUrl}/chat/lock/false`, "POST", body(channel));

		const res = await fetch(`${baseUrl}/end/check`, "POST", body(channel));

		if (res.status === 204) {
			await fetch(`${baseUrl}/end`, "POST", body(channel));

			return true;
		}

		return false;
	},
};
