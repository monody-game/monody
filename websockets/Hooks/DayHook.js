import fetch from "../Helpers/fetch.js";
import Body from "../Helpers/Body.js";

export default {
	identifier: 6,
	async before(io, channel) {
		const body = Body.make({
			gameId: channel.split(".")[1]
		});

		await fetch("https://web/api/game/message/deaths", {
			method: "POST",
			body,
		});
	}
};
