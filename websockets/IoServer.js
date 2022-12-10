import { Server } from "socket.io";
import { RedisSubscriber } from "./Redis/RedisSubscriber.js";
import { ResponderManager } from "./Responders/ResponderManager.js";
import { Channel } from "./Channels/Channel.js";
import { createSecureServer } from "node:http2";
import { readFileSync } from "node:fs";
import { GameService } from "./Services/GameService.js";
import { gameId } from "./Helpers/Functions.js";

export class IoServer {
	responders = [];

	constructor() {
		this.httpServer = createSecureServer({
			allowHTTP1: true,
			key: readFileSync("/var/www/cert.key"),
			cert: readFileSync("/var/www/cert.pem")
		});
		this.server = new Server(this.httpServer, {
			cors: {
				credentials: true
			}
		});
		this.subscriber = new RedisSubscriber();
		this.channel = new Channel(this.server);
	}

	async start() {
		console.info("\nStarting IoServer...");

		if (process.env.APP_DEBUG) {
			console.info("\nIoServer is running in debug mode.\n");
		}

		await this.initResponders();
		this.onConnect();
		await this.listen();
		this.httpServer.listen(6001);
	}

	async listen() {
		await this.subscriber.subscribe(async (channel, message) => {
			if (message.data.private === true) {
				const members = await GameService.getMembers(gameId(channel));

				for (let caller of message.data.emitters) {
					caller = members.find(member => member.user_id === caller);
					this.server.to(caller.socketId).emit(message.event, channel, { data: { payload: message.data.payload } });
				}

				return;
			}

			this.broadcast(channel, message);
		});
	}

	find(id) {
		return this.server.sockets.sockets[id];
	}

	broadcast(channel, message) {
		if (message.socket && this.find(message.socket)) {
			this.find(message.socket).broadcast.to(channel).emit(message.event, channel, message.data);
		} else {
			this.server.to(channel).emit(message.event, channel, message);
		}
	}

	onConnect() {
		this.server.on("connection", (socket) => {
			this.onSubscribe(socket);
			this.onUnsubscribe(socket);
			this.onDisconnecting(socket);
			this.onClientEvent(socket);
		});
	}

	onSubscribe(socket) {
		socket.on("subscribe", async (data) => {
			if (data.channel) {
				const responder = ResponderManager.findResponder("subscribe", this.responders);
				await responder.emit(socket, data);
			}
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

	onClientEvent(socket) {
		socket.on("client event", async (data) => {
			if (data.event && data.channel) {
				const responder = ResponderManager.findResponder(data.event, this.responders);
				if (responder) await responder.emit(socket, data);
			}
		});
	}

	async initResponders() {
		const responders = ResponderManager.getAll();
		await Promise.all(responders);
		for (let responder of responders) {
			responder = await responder;
			this.responders.push(new responder.default(this.server));
		}
	}
}
