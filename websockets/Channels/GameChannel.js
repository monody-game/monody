import { StateManager } from "../Services/StateManager.js";
import { client } from "../Redis/Connection.js";
import { GameService } from "../Services/GameService.js";
import { CounterService } from "../Services/CounterService.js";
import fetch from "../Helpers/fetch.js";
import Body from "../Helpers/Body.js";
import { gameId } from "../Helpers/Functions.js";
import { ChatService } from "../Services/ChatService.js";

const StartingState = (await fetch("https://web/api/state/0", { "method": "GET" })).json;
const WaitingState = (await fetch("https://web/api/state/1", { "method": "GET" })).json;

export class GameChannel {
	constructor(io) {
		this.io = io;
		this.gameService = new GameService(io);
		this.stateManager = new StateManager(io);
		this.counterService = new CounterService(io);
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
			this.onJoin(socket, channel, member);
		}

		const params = Body.make({
			gameId: id,
			userId: member.user_id
		});

		await fetch("https://web/api/game/join", {
			method: "POST",
			body: params
		});

		const count = await this.gameService.getRolesCount(id);
		const game = await GameService.getGame(id);

		setTimeout(async () => {
			this.io.to(socket.id).emit("game.data", channel, await this.stateManager.getState(gameId(channel)));
			ChatService.send(socket, channel, "t'as rej bg", "message", socket.id);
		}, 100);

		if (members.length === count && game.is_started === false) {
			await this.gameService.startGame(channel, game, members, socket);
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
			await this.gameService.stopGameLaunch(channel);
			game.is_started = false;
		}

		const member = members.find(m => m.socketId === socket.id);
		if (!member) return;
		members = members.filter(m => m.socketId !== member.socketId);

		const params = Body.make({
			userId: member.user_id
		});

		await fetch("https://web/api/game/leave", {
			method: "POST",
			body: params
		});

		if (members.length === 0) {
			this.onLeave(channel, member);
			await this.onDelete(id);
		} else {
			await client.set(`game:${id}:members`, JSON.stringify(members));
			game.users = members;
			await this.gameService.setGame(id, game);

			const isMember = await this.isMember(channel, member);

			if (!isMember && member) {
				delete member.socketId;
				this.onLeave(channel, member);
			}
		}
	}

	onJoin(socket, channel, member) {
		socket.broadcast.to(channel).emit("presence:joining", channel, member);
	}

	onLeave(channel, member) {
		this.io.to(channel).emit("presence:leaving", channel, member);
	}

	async onDelete(id) {
		this.gameService.stopTimeouts(id);

		await fetch("https://web/api/game", {
			method: "DELETE",
			body: Body.make({ gameId: id })
		});

		this.io.to("home").emit("game.delete", "home", id);

		console.info(`[${new Date().toISOString()}] - Deleting game, id: ${id}`);
	}

	async onSubscribed(socket, channel, members) {
		this.io.to(socket.id).emit("presence:subscribed", channel, members);
	}
}
