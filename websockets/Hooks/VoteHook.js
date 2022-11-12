import fetch from "../Helpers/fetch.js";
import { client } from "../Redis/Connection.js";

export default {
	identifier: 7,
	async before(io, channel, members) {
		const gameId = channel.split(".")[1];
		const params = new URLSearchParams();
		params.set("gameId", gameId);
		params.set("type", "vote");

		let interactionId = await fetch("https://web/api/interactions", { method: "POST", body: params });
		interactionId = interactionId.json.interaction.id;
		console.log(`Created vote interaction with id ${interactionId} in game ${gameId}`);

		members.forEach(member => {
			io.to(member.socketId).emit("interaction.open", { interactionId }, channel);
		});
	},
	async after(io, channel, members) {
		const gameId = channel.split(".")[1];
		let interactions = JSON.parse(await client.get(`game:${gameId}:interactions`));

		interactions = interactions.filter(interaction => interaction.type === "vote")[0];
		const params = new URLSearchParams();
		params.set("gameId", gameId);
		params.set("id", interactions.id);

		console.log(`Closing vote interaction with id ${interactions.id} in game ${gameId}`);
		await fetch("https://web/api/interactions", { method: "DELETE", body: params });

		members.forEach(member => {
			io.to(member.socketId).emit("interaction.close", { interactionId: interactions.id }, channel);
		});
	},
};
