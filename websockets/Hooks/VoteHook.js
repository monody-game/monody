import { InteractionService } from "../Services/InteractionService.js";
import Body from "../Helpers/Body.js";
import fetch from "../Helpers/fetch.js";

export default {
	identifier: 7,
	async before(io, channel) {
		await InteractionService.openInteraction(io, channel, "vote");
		return false;
	},
	async after(io, channel) {
		await InteractionService.closeInteraction(io, channel, "vote");
		const body = Body.make({
			gameId: channel.split(".")[1]
		});
		const baseURL = "https://web/api/game";

		await fetch(`${baseURL}/message/deaths`, {
			method: "POST",
			body,
		});

		const res = await fetch(`${baseURL}/end/check`, {
			method: "POST",
			body
		});

		if (res.status === 204) {
			await fetch(`${baseURL}/end`, {
				method: "POST",
				body
			});

			return true;
		}

		return false;
	},
};
