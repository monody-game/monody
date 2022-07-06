const fetch = require("../../Helpers/fetch");

module.exports = {
	name: "vote",
	duration: 40,
	identifier: 5,
	before(io, channel, members) {
		members.forEach(member => {
			io.to(member.socketId).emit("vote.open", channel);
		});
	},
	async after(io, channel, members) {
		members.forEach(member => {
			io.to(member.socketId).emit("vote.close", channel);
		});

		const params = new URLSearchParams();
		params.set("gameId", channel.split(".")[1]);

		const res = await fetch("https://web/api/game/aftervote", {
			method: "POST",
			body: params
		});

		console.log(res);
	},
};
