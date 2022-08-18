export default {
	name: "werewolves",
	duration: 20,
	identifier: 3,
	before(socket, channel) {
		socket.emit("vote.open", channel);
	},
	after(socket, channel) {
		socket.emit("vote.close", channel);
	},
};
