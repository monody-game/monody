import fetch from "../Helpers/fetch.js";
import { gameId } from "../Helpers/Functions.js";
import { client } from "../Redis/Connection.js";
import { InteractionService } from "../Services/InteractionService.js";
import {Server} from "socket.io";
import {State} from "../Services/StateManager";

export default {
	identifier: 6,
	async before(io: Server, channel: string) {
		const id = gameId(channel);
		const body = {
			gameId: id
		};

		const baseUrl = `${process.env.API_URL}/game`;

		const interactions = JSON.parse(await client.get(`game:${id}:interactions`) as string);
		const interaction = interactions.find((interactionListItem: {type: string}) => interactionListItem.type === "angel");

		if (interaction) {
			const res = await fetch(`${baseUrl}/interactions/status`, "POST", { gameId: id, type: "angel" });

			if (res.json === true) {
				await InteractionService.closeInteraction(io, channel, "angel");
			}
		}

		const game = JSON.parse(await client.get(`game:${id}`) as string);
		const state: State = JSON.parse(await client.get(`game:${id}:state`) as string);

		if ("bitten" in game && (state.round ?? 0) - game["bitten"].round === 1) {
			await fetch(`${baseUrl}/kill`, "POST", {
				userId: game["bitten"].target,
				gameId: id,
				context: 'bitten'
			});
		}

		await fetch(`${baseUrl}/chat/lock`, "POST", body);

		await fetch(`${baseUrl}/message/deaths`, "POST", body);

		const res = await fetch(`${baseUrl}/end/check`, "POST", body);

		if (res.status === 204) {
			await fetch(`${baseUrl}/end`, "POST", body);

			return true;
		}

		return false;
	}
};
