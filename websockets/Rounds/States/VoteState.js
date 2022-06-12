module.exports = {
	name: "vote",
	duration: 120,
	identifier: 5,
	before(io, channel, members) {
		members.forEach(member => {
			io.to(member.socketId).emit("vote.open", channel);
		});
	},
	after(io, channel, members) {
		members.forEach(member => {
			io.to(member.socketId).emit("vote.close", channel);
		});
	},
};
