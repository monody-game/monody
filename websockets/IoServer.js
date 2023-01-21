import { Server } from "socket.io";
import { RedisSubscriber } from "./Redis/RedisSubscriber.js";
import { Channel } from "./Channels/Channel.js";
import { createSecureServer } from "node:http2";
import { readFileSync } from "node:fs";
import { GameService } from "./Services/GameService.js";
import { gameId } from "./Helpers/Functions.js";
import { handle } from "./PrivateEventHandler.js";
import { EventEmitter } from "node:events";
import { info, success, warn, blank } from "./Logger.js";

export class IoServer {
	constructor() {
		this.httpServer = createSecureServer({
			allowHTTP1: true,
			cert: readFileSync(process.env.CERT_PATH),
			key: readFileSync(process.env.CERT_PRIVATE_KEY_PATH),
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
		await this.subscriber.subscribe(async (channel, message) => {
			if (channel === "ws.private") {
				await handle(this.emitter, message);
				return;
			}

			if (message.data.private !== true) {
				this.broadcast(channel, message);

				return;
			}

			const members = await GameService.getMembers(gameId(channel));

			for (let caller of message.data.emitters) {
				caller = members.find(member => member.user_id === caller);
				if (caller) {
					this.server.to(caller.socketId).emit(message.event, channel, { data: { payload: message.data.payload } });
				}
			}
		});
	}

	find(id) {
		return this.server.sockets.sockets[id];
	}

	broadcast(channel, message) {
		if (message.data.volatile) {
			if (message.socket && this.find(message.socket)) {
				this.find(message.socket).to(channel).broadcast.volatile.emit(message.event, channel, message.data);
			} else {
				this.server.to(channel).volatile.emit(message.event, channel, message);
			}
			return;
		}

		if (message.socket && this.find(message.socket)) {
			this.find(message.socket).to(channel).broadcast.emit(message.event, channel, message.data);
		} else {
			this.server.to(channel).emit(message.event, channel, message);
		}
	}

	onConnect() {
		info("Setting up join / leave hooks");
		info("Listening to ping event ...");
		this.server.on("connection", (socket) => {
			this.onSubscribe(socket);
			this.onUnsubscribe(socket);
			this.onDisconnecting(socket);

			socket.on("ping", (callback) => {
				if (typeof callback === "function") callback();
			});
		});
	}

	onSubscribe(socket) {
		socket.on("subscribe", async (data) => {
			await this.channel.join(socket, data);
		});
	}

	onUnsubscribe(socket) {
		socket.on("unsubscribe", async (data) => {
			await this.channel.leave(socket, data.channel, "unsubscribed");
		});
	}

	onDisconnecting(socket) {
		socket.on("disconnecting", (reason) => {
			socket.rooms.forEach(async (room) => {
				if (room !== socket.id) {
					await this.channel.leave(socket, room, reason);
				}
			});
		});
	}
}
