const { GameChannel } = require("./GameChannel");
const { PrivateChannel } = require("./PrivateChannel");
const { ResponderManager } = require("../Responders/ResponderManager");

module.exports.Channel = class {
	privateChannels = ["private-*", "presence-*"];

	clientEvents = ["client-*"];

	constructor(io) {
		this.private = new PrivateChannel();
		this.presence = new GameChannel(io);
		this.responderManager = new ResponderManager();
		this.io = io;
	}

	async join(socket, data) {
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

	async leave(socket, channel, reason) {
		if (channel) {
			if (this.isPresence(channel)) {
				await this.presence.leave(socket, channel);
			}

			socket.leave(channel);

			if (process.env.APP_DEBUG) {
				console.info(`[${new Date().toISOString()}] - ${socket.id} left channel: ${channel} (${reason})`);
			}
		}
	}

	isPrivate(channel) {
		let isPrivate = false;

		this.privateChannels.forEach(privateChannel => {
			const regex = new RegExp(privateChannel.replace("\*", ".*"));
			if (regex.test(channel)) isPrivate = true;
		});

		return isPrivate;
	}

	async joinPrivate(socket, data) {
		try {
			const res = JSON.parse(await this.private.authenticate(socket, data));
			socket.join(data.channel);

			if (this.isPresence(data.channel)) {
				let member = res.channel_data;

				try {
					member = JSON.parse(res.channel_data);
				} catch (e) {
				}

				await this.presence.join(socket, data.channel, member);
			}

			this.onJoin(socket, data.channel);
		} catch (e) {
			if (process.env.APP_DEBUG) {
				console.error(e);
			}
			this.io.sockets.to(socket.id).emit("subscription_error", data.channel, e.status);
		}
	}

	isPresence(channel) {
		return channel.lastIndexOf("presence-", 0) === 0;
	}

	onJoin(socket, channel) {
		if (process.env.APP_DEBUG) {
			console.info(`[${new Date().toISOString()}] - ${socket.id} joined channel: ${channel}`);
		}
	}

	isClientEvent(event) {
		let isClientEvent = false;

		this.clientEvents.forEach(clientEvent => {
			const regex = new RegExp(clientEvent.replace("*", ".*"));
			if (regex.test(event)) isClientEvent = true;
		});

		return isClientEvent;
	}

	isInChannel(socket, channel) {
		return socket.rooms.has(channel);
	}
};
