import { GameChannel } from "./GameChannel.js";
import { PrivateChannel } from "./PrivateChannel.js";
import { error, info } from "../Logger.js";
import { Server, Socket } from "socket.io";
import { EventEmitter } from "node:events";
import { DataPayload } from "../IoServer.js";

export class Channel {
	private privateChannels = ["private-*", "presence-*"];
	private private: PrivateChannel;
	private presence: GameChannel;

	constructor(io: Server, emitter: EventEmitter) {
		this.private = new PrivateChannel();
		this.presence = new GameChannel(io, emitter);
	}

	async join(socket: Socket, data: DataPayload) {
		if (data.channel) {
			if (this.isInChannel(socket, data.channel)) return;
			if (this.isPrivate(data.channel)) {
				await this.joinPrivate(socket, data);
			} else {
				socket.join(data.channel);
				this.onJoin(socket, data.channel);
			}
		}
	}

	async leave(socket: Socket, channel: string, reason: string) {
		if (this.isPresence(channel)) {
			await this.presence.leave(socket, channel);
		}

		socket.leave(channel);

		if (process.env.APP_DEBUG) {
			info(`${socket.id} left channel: ${channel} (${reason})`);
		}
	}

	isPrivate(channel: string): boolean {
		let isPrivate = false;

		this.privateChannels.forEach((privateChannel) => {
			const regex = new RegExp(privateChannel.replace("*", ".*"));
			if (regex.test(channel)) isPrivate = true;
		});

		return isPrivate;
	}

	async joinPrivate(socket: Socket, data: DataPayload) {
		try {
			const res = JSON.parse(
				await this.private.authenticate(socket, data),
			);
			socket.join(data.channel);

			if (this.isPresence(data.channel)) {
				let member = res.channel_data;

				if (typeof res.channel_data === "string") {
					try {
						member = JSON.parse(res.channel_data);
					} catch (e) {
						error(e);
					}
				}

				await this.presence.join(socket, data.channel, member);
			}

			this.onJoin(socket, data.channel);
		} catch (e) {
			if (process.env.APP_DEBUG) {
				error(`Error during user joining channel ${data.channel}`);
				error(e);
			}
		}
	}

	isPresence(channel: string) {
		return channel.lastIndexOf("presence-", 0) === 0;
	}

	onJoin(socket: Socket, channel: string) {
		if (process.env.APP_DEBUG) {
			info(`${socket.id} joined channel: ${channel}`);
		}
	}

	isInChannel(socket: Socket, channel: string) {
		return socket.rooms.has(channel);
	}
}
