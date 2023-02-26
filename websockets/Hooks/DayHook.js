import fetch from "../Helpers/fetch.js";
import Body from "../Helpers/Body.js";
import { gameId } from "../Helpers/Functions.js";
import { client } from "../Redis/Connection.js";
import { InteractionService } from "../Services/InteractionService.js";

export default {
	identifier: 6,
	async before(io, channel) {
		const id = gameId(channel);
		const body = Body.make({
			gameId: id
		});

		const baseUrl = `${process.env.API_URL}/game`;

		const interactions = JSON.parse(await client.get(`game:${id}:interactions`));
		const interaction = interactions.find(interactionListItem => interactionListItem.type === "angel");

		if (interaction) {
			const res = await fetch(`${baseUrl}/interactions/status`, {
				method: "POST",
				body: Body.make({
					gameId: id,
					type: "angel"
				})
			});

			if (res.json === true) {
				await InteractionService.closeInteraction(io, channel, "angel");
			}
		}

		await fetch(`${baseUrl}/chat/lock`, {
			method: "POST",
			body
		});

		await fetch(`${baseUrl}/message/deaths`, {
			method: "POST",
			body,
		});

		const res = await fetch(`${baseUrl}/end/check`, {
			method: "POST",
			body
		});

		if (res.status === 204) {
			await fetch(`${baseUrl}/end`, {
				method: "POST",
				body
			});

			return true;
		}

		return false;
	}
};
