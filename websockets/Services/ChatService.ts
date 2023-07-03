import { BroadcastOperator } from "socket.io/dist/broadcast-operator";
import {
	DecorateAcknowledgementsWithMultipleResponses,
	DefaultEventsMap,
} from "socket.io/dist/typed-events";
import { Server } from "socket.io";

type Socket = BroadcastOperator<
	DecorateAcknowledgementsWithMultipleResponses<DefaultEventsMap>,
	DefaultEventsMap
>;

type MessageType = (
	socket: Socket | Server,
	channel: string,
	message: string,
	to?: string | null
) => void;

export class ChatService {
	static send(
		socket: Socket | Server,
		channel: string,
		message: string,
		type = "message",
		to: string | null = null
	) {
		if (to) {
			socket = socket.to(to);
		}

		socket.emit("chat.send", channel, {
			data: {
				payload: {
					content: message,
					type,
				},
			},
		});
	}

	public static message: MessageType = (
		socket,
		channel,
		message,
		to = null
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
		to = null
	) => {
		this.send(socket, channel, message, "error", to);
	};
}
