import fetch from "../Helpers/fetch.js";
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

		console.log(`Created ${type} interaction with id ${interactionId} in game ${gameId}`);

		io.to(channel).emit("interaction.open", channel, { interaction: interaction });
	}

	static async closeInteraction(io, channel, interactionId, type) {
		const gameId = channel.split(".")[1];
		const params = new URLSearchParams();
		params.set("gameId", gameId);
		params.set("id", interactionId);

		await fetch("https://web/api/interactions", { method: "DELETE", body: params });

		console.log(`Closing ${type} interaction with id ${interactionId} in game ${gameId}`);

		io.to(channel).emit("interaction.close", channel, { interaction: { type, id: interactionId } });
	}
}
