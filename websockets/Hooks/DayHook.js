import fetch from "../Helpers/fetch.js";
import Body from "../Helpers/Body.js";

export default {
	identifier: 6,
	async before(io, channel) {
		const body = Body.make({
			gameId: channel.split(".")[1]
		});
		const baseUrl = "https://web/api/game/";

		await fetch(`${baseUrl}message/deaths`, {
			method: "POST",
			body,
		});

		await fetch(`${baseUrl}/chat/lock`, {
			method: "POST",
			body
		});
	}
};
