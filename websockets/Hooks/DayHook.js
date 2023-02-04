import fetch from "../Helpers/fetch.js";
import Body from "../Helpers/Body.js";
import { gameId } from "../Helpers/Functions.js";

export default {
	identifier: 6,
	async before(io, channel) {
		const body = Body.make({
			gameId: gameId(channel)
		});
    
		const baseUrl = `${process.env.API_URL}/game`;

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
