const { client } = require("../Redis/Connection");
const StartingState = require("../Rounds/States/StartingState");
const WaitingState = require("../Rounds/States/WaitingState");

module.exports.PresenceChannel = class {
	constructor(io) {
		this.io = io;
		this.gameService = new (require("../Services/GameService"))(io);
		this.stateManager = new (require("../Services/StateManager"))(io);
		this.counterService = new (require("../Services/CounterService"))(io);
	}

	async getMembers(channel) {
		const members = JSON.parse(await client.get(`game:${channel.split(".")[1]}:members`));
		if (!members) return [];
		return members;
	}

	async isMember(channel, member) {
		let members = await this.getMembers(channel);
		members = await this.removeInactive(channel, members);
		const search = members.filter(m => m.user_id === member.user_id);
		return search && search.length;
	}

	async removeInactive(channel, members) {
		const clients = await this.io.of("/").in(channel).allSockets();
		members = members.filter(member => {
			return Array.from(clients).indexOf(member.socketId) >= 0;
		});
		await client.set(`game:${channel.split(".")[1]}:members`, JSON.stringify(members));
		return members;
	}

	async join(socket, channel, member) {
		if (!member) {
			return;
		}

		const isMember = await this.isMember(channel, member);
		const members = await this.getMembers(channel);

		member.socketId = socket.id;
		members.push(member);

		await client.set(`game:${channel.split(".")[1]}:members`, JSON.stringify(members));

		this.onSubscribed(socket, channel, members);

		if (!isMember) {
			this.onJoin(socket, channel, member);
		}

		const id = channel.split(".")[1];
		const count = await this.gameService.getRolesCount(id);
		const game = JSON.parse(await client.get("game:" + id));

		if (
			await this.gameService.isAuthor(socket, id) &&
      !await this.gameService.getGame(id).is_started &&
      !members.length > 0) {
			this.stateManager.setState({
				status: WaitingState.identifier,
				startTimestamp: Date.now(),
				counterDuration: WaitingState.duration
			}, channel);
		}

		if (members.length === count && game.is_started === false) {
			await this.gameService.startGame(channel, game, members);
		}
	}

	async leave(socket, channel) {
		const gameId = channel.split(".")[1];
		const game = await this.gameService.getGame(gameId);
		let members = await this.getMembers(channel);
		members = members || [];

		if (!game) return;

		if (game.is_started) {
			const state = await this.stateManager.getState(gameId);
			if (state.status === StartingState.identifier) {
				await this.gameService.stopGameLaunch(channel);
				game.is_started = false;
			}
		}

		const member = members.find(m => m.socketId === socket.id);
		if (!member) return;
		members = members.filter(m => m.socketId !== member.socketId);

		if (members.length === 0) {
			this.onLeave(channel, member);
			await this.onDelete(gameId);
		} else {
			await client.set(`game:${channel.split(".")[1]}:members`, JSON.stringify(members));
			game.users = members;
			await this.gameService.setGame(gameId, game);

			const isMember = await this.isMember(channel, member);

			if (!isMember && member) {
				delete member.socketId;
				this.onLeave(channel, member);
			}
		}
	}

	onJoin(socket, channel, member) {
		this.io.sockets.sockets.get(socket.id).broadcast.to(channel).emit("presence:joining", channel, member);
	}

	onLeave(channel, member) {
		this.io.to(channel).emit("presence:leaving", channel, member);
	}

	async onDelete(gameId) {
		await client.del(`game:${gameId}:members`);
		await client.del(`game:${gameId}`);
		await client.del(`game:${gameId}:state`);

		clearTimeout(this.counterService.counterId);

		this.io.to("home").emit("game.delete", "home", gameId);

		console.info(`Deleting game, id: ${gameId}`);
	}

	onSubscribed(socket, channel, members) {
		this.io.to(socket.id).emit("presence:subscribed", channel, members);
	}
};
