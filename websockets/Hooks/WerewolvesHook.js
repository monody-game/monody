import { InteractionService } from "../Services/InteractionService.js";
import fetch from "../Helpers/fetch.js";
import Body from "../Helpers/Body.js";
const baseURL = "https://web/api/game";

export default {
	identifier: 3,
	async before(io, channel) {
		await InteractionService.openInteraction(io, channel, "werewolves");

		await fetch(`${baseURL}/chat/lock`, {
			method: "POST",
			body: Body.make({
				gameId: channel.split(".")[1],
				team: "2"
			})
		});

		return false;
	},
	async after(io, channel) {
		await InteractionService.closeInteraction(io, channel, "werewolves");

		await fetch(`${baseURL}/chat/lock`, {
			method: "POST",
			body: Body.make({
				gameId: channel.split(".")[1],
				team: "2"
			})
		});

		return false;
	},
};
