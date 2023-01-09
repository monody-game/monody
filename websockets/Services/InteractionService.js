import fetch from "../Helpers/fetch.js";
import { GameService } from "./GameService.js";
import { client } from "../Redis/Connection.js";
import Body from "../Helpers/Body.js";
import { gameId } from "../Helpers/Functions.js";
import { error, info, success, warn } from "../Logger.js";

export class InteractionService {
	static async openInteraction(io, channel, type) {
		const id = gameId(channel);
		const params = Body.make({
			gameId: id,
			type
		});
		const res = await fetch("https://web/api/interactions", { method: "POST", body: params });

		try {
			const interaction = res.json.interaction;

			const interactionId = interaction.id;
			let callers = interaction.authorizedCallers;
			let data = null;

			if (interaction.data) {
				data = interaction.data;
			}

			success(`Created ${type} interaction with id ${interactionId} in game ${id}.`);

			if (callers !== "*") {
				callers = JSON.parse(callers);
				callers = [...callers];
				const members = await GameService.getMembers(id);

				for (let caller of callers) {
					caller = members.find(member => member.user_id === caller);
					io.to(caller.socketId).emit("interaction.open", channel, { interaction: { id: interactionId, type, data } });
				}

				return;
			}

			io.to(channel).emit("interaction.open", channel, { interaction: { id: interactionId, type, data } });
		} catch (e) {
			error(e);
			error("Error openning interaction, API response : ", res);
		}
	}

	static async closeInteraction(io, channel, type) {
		const id = gameId(channel);

		const interactions = JSON.parse(await client.get(`game:${id}:interactions`));

		if (interactions === []) {
			return;
		}

		const interaction = interactions.find(interactionListItem => interactionListItem.type === type);

		if (!interaction) {
			warn(`Unable to find interaction with type ${type} on game ${id}, aborting.`);
			return;
		}

		const interactionId = interaction.id;
		let callers = interaction.authorizedCallers;

		const params = Body.make({
			gameId: id,
			id: interactionId
		});

		await fetch("https://web/api/interactions", { method: "DELETE", body: params });

		info(`Closing ${type} interaction with id ${interactionId} in game ${id}.`);

		if (callers !== "*") {
			callers = JSON.parse(callers);
			const members = await GameService.getMembers(id);

			for (let caller of callers) {
				caller = members.find(member => member.user_id === caller);
				io.to(caller.socketId).emit("interaction.close", channel, { interaction: { type } });
			}

			return;
		}

		io.to(channel).emit("interaction.close", channel, { interaction: { type } });
	}
}
