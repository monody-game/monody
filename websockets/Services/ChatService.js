export class ChatService {
	static send(socket, channel, message, type = "message", to = null) {
		if (to) {
			socket = socket.to(to);
		}

		socket.to(to).emit("chat.send", channel, {
			data: {
				message,
				type
			}
		});
	}

	static message(socket, channel, message, to = null) {
		this.send(socket, channel, message, undefined, to);
	}

	static success(socket, channel, message, to = null) {
		this.send(socket, channel, message, "success", to);
	}

	static info(socket, channel, message, to = null) {
		this.send(socket, channel, message, "info", to);
	}

	static warn(socket, channel, message, to = null) {
		this.send(socket, channel, message, "warn", to);
	}

	static error(socket, channel, message, to = null) {
		this.send(socket, channel, message, "error", to);
	}
}
