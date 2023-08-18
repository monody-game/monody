import { Server } from "socket.io";

type MessageType = (
	socket: Server,
	channel: string,
	message: string,
	type?: string | null,
	to?: string | null,
) => void;

export class ChatService {
	public static send: MessageType = (
		socket,
		channel,
		message,
		type = "message",
		to = null,
	) => {
		if (to) {
			socket.to(to).emit("chat.send", channel, {
				data: {
					payload: {
						content: message,
						type,
					},
				},
			});

			return;
		}

		socket.emit("chat.send", channel, {
			data: {
				payload: {
					content: message,
					type,
				},
			},
		});
	};

	public static message: MessageType = (
		socket,
		channel,
		message,
		to = null,
	) => {
		this.send(socket, channel, message, undefined, to);
	};

	public static info: MessageType = (socket, channel, message, to = null) => {
		this.send(socket, channel, message, "info", to);
	};

	public static error: MessageType = (
		socket,
		channel,
		message,
		to = null,
	) => {
		this.send(socket, channel, message, "error", to);
	};
}
