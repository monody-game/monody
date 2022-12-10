import { client } from "../Redis/Connection.js";
import { StateManager } from "./StateManager.js";
import { CounterService } from "./CounterService.js";
import { UserService } from "./UserService.js";
import { ChatService } from "./ChatService.js";
import fetch from "../Helpers/fetch.js";
import Body from "../Helpers/Body.js";
import { gameId } from "../Helpers/Functions.js";

const WaitingState = (await fetch("https://web/api/state/0", { "method": "GET" })).json;
const StartingState = (await fetch("https://web/api/state/1", { "method": "GET" })).json;

export class GameService {
	timeouts = {};

	constructor(io) {
		this.io = io;
		this.StateManager = new StateManager(io);
		this.counterService = new CounterService(io);
	}

	static async getGame(id) {
		return JSON.parse(await client.get("game:" + id));
	}

	static async exists(id) {
		return await client.exists("game:" + id);
	}

	static async getMembers(id) {
		return JSON.parse(await client.get(`game:${id}:members`));
	}

	async setGame(id, data) {
		await client.set("game:" + id, JSON.stringify(data));
	}

	static async isAuthor(socket, id) {
		const game = await GameService.getGame(id);
		if (!game) return false;

		const members = JSON.parse(await client.get("game:" + id + ":members")) ?? [];
		const user = UserService.getUserBySocket(socket, members);

		if (!user) return false;

		return game.owner === user.user_id;
	}

	async startGame(channel, game, members, socket) {
		game.is_started = true;
		const id = gameId(channel);
		await this.setGame(id, game);

		this.StateManager.setState({
			status: StartingState.state,
			startTimestamp: Date.now(),
			counterDuration: StartingState.duration
		}, channel);

		if (process.env.APP_DEBUG) {
			console.info(`[${new Date().toISOString()}] - Starting game id ${id}\n`);
		}

		this.timeouts[id] = [setTimeout(async () => {
			await this.roleManagement(channel, members);
		}, 6000)];

		this.timeouts[id].push(setTimeout(async () => {
			await this.counterService.cycle(channel, socket);
		}, 11000));
	}

	async stopGameLaunch(channel) {
		const id = gameId(channel);
		this.stopTimeouts(id);

		await this.StateManager.setState({
			status: WaitingState.state,
			startTimestamp: Date.now(),
			counterDuration: WaitingState.duration
		}, channel);
	}

	async roleManagement(channel, members) {
		const id = gameId(channel);
		const params = Body.make({
			gameId: id
		});

		await fetch("https://web/api/roles/assign", { method: "POST", body: params });
		const game = await GameService.getGame(id);

		for (const member of members) {
			const user = UserService.getUserBySocket(member.socketId, members);
			const roleId = game.assigned_roles[user.user_id];
			let role = await fetch(`https://web/api/roles/get/${roleId}`, { method: "GET" });

			role = role.json.role;
			this.io.to(member.socketId).emit("game.role-assign", channel, game.assigned_roles[user.user_id]);
			ChatService.info(this.io, channel, `Votre role est : ${role.display_name}`, member.socketId);
		}
	}

	async getRolesCount(id) {
		const game = await GameService.getGame(id);
		if (!game) return;
		let count = 0;

		for (const role in game.roles) {
			count += game.roles[role];
		}
		return count;
	}

	stopTimeouts(id) {
		this.counterService.stop(id);
		if (this.timeouts[id] && this.timeouts[id].length > 0) {
			this.timeouts[id].forEach(clearTimeout);
		}
	}
}
