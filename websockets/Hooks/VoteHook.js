import { InteractionService } from "../Services/InteractionService.js";
import { client } from "../Redis/Connection.js";

export default {
	identifier: 7,
	async before(io, channel) {
		await InteractionService.openInteraction(io, channel, "vote");
	},
	async after(io, channel) {
		const interactions = JSON.parse(await client.get(`game:${channel.split(".")[1]}:interactions`));
		const interactionId = interactions.find(interaction => interaction.type === "vote").id;

		await InteractionService.closeInteraction(io, channel, interactionId, "vote");
	},
};
