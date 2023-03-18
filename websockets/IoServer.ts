import { readFileSync } from "node:fs";
import { createSecureServer, Http2SecureServer } from "node:http2";
import { EventEmitter } from "node:events";
import {Server, Socket} from "socket.io";
import { RedisSubscriber } from "./Redis/RedisSubscriber.js";
import { Channel } from "./Channels/Channel.js";
import { GameService } from "./Services/GameService.js";
import { gameId } from "./Helpers/Functions.js";
import { handle } from "./PrivateEventHandler.js";
import { info, success, warn, blank } from "./Logger.js";

type EventPayload = {
	data: {
		recipients?: string[]
		private?: boolean
		volatile?: boolean
		payload: object|string
	}
	event: string
	socket: string
}

type DataPayload = {
	channel: string
	auth: {
		headers: {
			[key: string]: string
		}
	}
}

export class IoServer {
	private readonly httpServer: Http2SecureServer;
	private readonly server: Server;
	private subscriber: RedisSubscriber;
	private readonly emitter: EventEmitter;
	private channel: Channel;

	constructor() {
		this.httpServer = createSecureServer({
			allowHTTP1: true,
			cert: readFileSync(process.env.CERT_PATH as string),
			key: readFileSync(process.env.CERT_PRIVATE_KEY_PATH as string),
		});
		this.server = new Server(this.httpServer, {
			cors: {
				credentials: true
			}
		});
		this.subscriber = new RedisSubscriber();
		this.emitter = new EventEmitter();
		this.channel = new Channel(this.server, this.emitter);
	}

	async start() {
		const startTime = Date.now();
		blank();
		info("Starting IoServer...");

		if (process.env.APP_DEBUG) {
			warn("IoServer is running in debug mode.");
		}

		this.onConnect();
		await this.listen();
		this.httpServer.listen(6001);
		const endTime = Date.now();
		success(`Successfully started websockets server in ${endTime - startTime}ms!`);
	}

	async listen() {
		info("Waiting for events to broadcast ...");
		await this.subscriber.subscribe(async (channel: string, message: EventPayload) => {
			if (channel === "ws.private") {
				await handle(this.emitter, message);
				return;
			}

			if (message.data.private !== true) {
				this.broadcast(channel, message);

				return;
			}

			const members = await GameService.getMembers(gameId(channel));

			if(!message.data.recipients) return;

			for (const caller of message.data.recipients) {
				const member = members.find(member => member.user_id === caller);

				if (member && member.socketId) {
					this.server.to(member.socketId).emit(message.event, channel, { data: { payload: message.data.payload } });
				}
			}
		});
	}

	find(id: string) {
		return this.server.sockets.sockets.get(id);
	}

	broadcast(channel: string, message: EventPayload) {
		if (message.data.volatile) {
			if (message.socket && this.find(message.socket)) {
				this.find(message.socket)?.to(channel).volatile.emit(message.event, channel, message.data);
			} else {
				this.server.to(channel).volatile.emit(message.event, channel, message);
			}
			return;
		}

		if (message.socket && this.find(message.socket)) {
			this.find(message.socket)?.to(channel).emit(message.event, channel, message.data);
		} else {
			this.server.to(channel).emit(message.event, channel, message);
		}
	}

	onConnect() {
		info("Setting up join / leave hooks");
		info("Listening to ping event ...");
		this.server.on("connection", (socket: Socket) => {
			this.onSubscribe(socket);
			this.onUnsubscribe(socket);
			this.onDisconnecting(socket);

			socket.on("ping", (callback) => {
				if (typeof callback === "function") callback();
			});
		});
	}

	onSubscribe(socket: Socket) {
		socket.on("subscribe", async (data: DataPayload) => {
			await this.channel.join(socket, data);
		});
	}

	onUnsubscribe(socket: Socket) {
		socket.on("unsubscribe", async (data: DataPayload) => {
			await this.channel.leave(socket, data.channel, "unsubscribed");
		});
	}

	onDisconnecting(socket: Socket) {
		socket.on("disconnecting", (reason) => {
			socket.rooms.forEach(async (room) => {
				if (room !== socket.id) {
					await this.channel.leave(socket, room, reason);
				}
			});
		});
	}
}

export { DataPayload }
