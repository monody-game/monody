module.exports = {
	name: "vote",
	duration: 20,
	identifier: 5,
	before(io, channel, members) {
		members.forEach(member => {
			console.log(member.socketId);
			io.to(member.socketId).emit("vote.open", channel);
		});
	},
	after(io, channel, members) {
		members.forEach(member => {
			console.log(member.socketId);
			io.to(member.socketId).emit("vote.close", channel);
		});
	},
};
