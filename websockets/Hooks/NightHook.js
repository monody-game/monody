import fetch from "../Helpers/fetch.js";
import Body from "../Helpers/Body.js";

export default {
	identifier: 2,
	async before(io, channel) {
		await fetch("https://web/api/game/chat/lock", {
			method: "POST",
			body: Body.make({ gameId: channel.split(".")[1] })
		});
	},
	async after(io, channel) {
		await fetch("https://web/api/game/chat/lock", {
			method: "POST",
			body: Body.make({ gameId: channel.split(".")[1] })
		});
	}
};
