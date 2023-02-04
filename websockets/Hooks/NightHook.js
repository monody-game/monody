import fetch from "../Helpers/fetch.js";
import Body from "../Helpers/Body.js";
import { gameId } from "../Helpers/Functions.js";

export default {
	identifier: 2,
	async before(io, channel) {
		await fetch(`${process.env.API_URL}/game/chat/lock`, {
			method: "POST",
			body: Body.make({ gameId: gameId(channel) })
		});
		return false;
	}
};
