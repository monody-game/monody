import fetch from "../Helpers/fetch.js";
import { GameService } from "./GameService.js";
import { client } from "../Redis/Connection.js";

export class InteractionService {
	static async openInteraction(io, channel, type) {
		const gameId = channel.split(".")[1];
		const params = new URLSearchParams();
		params.set("gameId", gameId);
		params.set("type", type);

		let interaction = await fetch("https://web/api/interactions", { method: "POST", body: params });
		interaction = interaction.json.interaction;
		const interactionId = interaction.id;
		let callers = interaction.authorizedCallers;

		console.log(`Created ${type} interaction with id ${interactionId} in game ${gameId}`);

		if (callers !== "*") {
			callers = JSON.parse(callers);
			const members = await GameService.getMembers(gameId);

			for (let caller of callers) {
				caller = members.find(member => member.user_id === caller);
				io.to(caller.socketId).emit("interaction.open", channel, { interaction: { id: interactionId, type } });
			}

			return;
		}

		io.to(channel).emit("interaction.open", channel, { interaction: { id: interactionId, type } });
	}

	static async closeInteraction(io, channel, type) {
		const gameId = channel.split(".")[1];

		const interactions = JSON.parse(await client.get(`game:${channel.split(".")[1]}:interactions`));
		const interaction = interactions.find(interactionListItem => interactionListItem.type === type);
		const interactionId = interaction.id;
		let callers = interaction.authorizedCallers;

		const params = new URLSearchParams();
		params.set("gameId", gameId);
		params.set("id", interactionId);

		await fetch("https://web/api/interactions", { method: "DELETE", body: params });

		console.log(`Closing ${type} interaction with id ${interactionId} in game ${gameId}`);

		if (callers !== "*") {
			callers = JSON.parse(callers);
			const members = await GameService.getMembers(gameId);

			for (let caller of callers) {
				caller = members.find(member => member.user_id === caller);
				io.to(caller.socketId).emit("interaction.close", channel, { interaction: { type } });
			}

			return;
		}

		io.to(channel).emit("interaction.close", channel, { interaction: { type } });
	}
}
