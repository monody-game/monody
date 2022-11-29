import { InteractionService } from "../Services/InteractionService.js";
import Body from "../Helpers/Body.js";
import fetch from "../Helpers/fetch.js";

export default {
	identifier: 7,
	async before(io, channel) {
		await InteractionService.openInteraction(io, channel, "vote");
	},
	async after(io, channel) {
		await InteractionService.closeInteraction(io, channel, "vote");
		const body = Body.make({
			gameId: channel.split(".")[1]
		});

		await fetch("https://web/api/game/message/deaths", {
			method: "POST",
			body,
		});
	},
};
