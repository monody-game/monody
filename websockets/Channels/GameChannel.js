import { StateManager } from "../Services/StateManager.js";
import { client } from "../Redis/Connection.js";
import { GameService } from "../Services/GameService.js";
import fetch from "../Helpers/fetch.js";
import Body from "../Helpers/Body.js";
import { gameId } from "../Helpers/Functions.js";
import { info, log } from "../Logger.js";

const StartingState = (await fetch(`${process.env.API_URL}/state/1`, { "method": "GET" })).json;

export class GameChannel {
	constructor(io, emitter) {
		this.io = io;
		this.gameService = new GameService(io, emitter);
		this.stateManager = new StateManager(io, emitter);
	}

	async getMembers(channel) {
		const members = JSON.parse(await client.get(`game:${gameId(channel)}:members`));
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
		await client.set(`game:${gameId(channel)}:members`, JSON.stringify(members));
		return members;
	}

	async join(socket, channel, member) {
		if (!member) {
			return;
		}

		const isMember = await this.isMember(channel, member);
		const members = await this.getMembers(channel);
		const id = gameId(channel);

		member.socketId = socket.id;
		members.push(member);

		await client.set(`game:${id}:members`, JSON.stringify(members));

		await this.onSubscribed(socket, channel, members);

		if (!isMember) {
			await this.onJoin(socket, channel, member);
		}

		const params = Body.make({
			gameId: id,
			userId: member.user_id
		});

		await fetch(`${process.env.API_URL}/game/join`, {
			method: "POST",
			body: params
		});

		const count = await this.gameService.getRolesCount(id);
		const game = await GameService.getGame(id);

		setTimeout(async () => {
			this.io.to(socket.id).emit("game.data", channel, await this.stateManager.getState(gameId(channel)));
		}, 100);

		if (members.length === count && game.is_started === false) {
			await this.gameService.startGame(channel, game, members, socket);

			const list = await fetch(`${process.env.API_URL}/game/list`, {
				method: "GET",
			});

			this.io.to("home").volatile.emit("game-list.update", "home", {
				data: list.json
			});
		}
	}

	async leave(socket, channel) {
		const id = gameId(channel);
		const game = await GameService.getGame(id);
		let members = await this.getMembers(channel);
		members = members || [];

		if (!game) return;

		const state = await this.stateManager.getState(id);
		if (!state) return;

		if (state.status === StartingState.state) {
			log(`Stopping starting state of game ${id}`);
			await this.gameService.stopGameLaunch(channel);
			game.is_started = false;
			await this.gameService.setGame(id, game);
		}

		const member = members.find(m => m.socketId === socket.id);
		if (!member) return;
		members = members.filter(m => m.socketId !== member.socketId);

		const params = Body.make({
			userId: member.user_id
		});

		await fetch(`${process.env.API_URL}/game/leave`, {
			method: "POST",
			body: params
		});

		if (members.length === 0) {
			await this.onLeave(channel, member);
			await this.onDelete(id);
		} else {
			await client.set(`game:${id}:members`, JSON.stringify(members));
			game.users = game.users.filter(userId => userId !== member.user_id);
			await this.gameService.setGame(id, game);

			const isMember = await this.isMember(channel, member);

			if (!isMember && member) {
				delete member.socketId;
				await this.onLeave(channel, member);
			}
		}
	}

	async onJoin(socket, channel, member) {
		socket.broadcast.to(channel).emit("presence:joining", channel, member);
		const list = await fetch(`${process.env.API_URL}/game/list`, {
			method: "GET",
		});

		this.io.to("home").volatile.emit("game-list.update", "home", {
			data: list.json
		});
	}

	async onLeave(channel, member) {
		this.io.to(channel).emit("presence:leaving", channel, member);
		const list = await fetch(`${process.env.API_URL}/game/list`, {
			method: "GET",
		});

		this.io.to("home").volatile.emit("game-list.update", "home", {
			data: list.json
		});
	}

	async onDelete(id) {
		await fetch(`${process.env.API_URL}/game`, {
			method: "DELETE",
			body: Body.make({ gameId: id })
		});

		const list = await fetch(`${process.env.API_URL}/game/list`, {
			method: "GET",
		});

		this.io.to("home").volatile.emit("game-list.update", "home", {
			data: list.json
		});

		log(`Deleting game with id: ${id}`);
	}

	async onSubscribed(socket, channel, members) {
		this.io.to(socket.id).emit("presence:subscribed", channel, members);
	}
}
