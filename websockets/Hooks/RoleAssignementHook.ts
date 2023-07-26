import { GameService } from "../Services/GameService.js";
import { InteractionService } from "../Services/InteractionService.js";
import { gameId } from "../Helpers/Functions.js";
import { client } from "../Redis/Connection.js";
import { Server } from "socket.io";
import fetch from "../Helpers/fetch.js";

export default {
	identifier: 9,
	async before(io: Server, channel: string) {
		await fetch(`${process.env.API_URL}/game/chat/lock/true`, "POST", {
			gameId: gameId(channel),
		});

		await GameService.roleManagement(io, channel);
		const game = JSON.parse(
			(await client.get(`game:${gameId(channel)}`)) as string,
		);

		// If there is an angel in the game
		if (Object.keys(game.roles).includes("9")) {
			await InteractionService.openInteraction(io, channel, "angel");
		}

		return false;
	},
};
