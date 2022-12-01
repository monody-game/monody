import { client } from "../Redis/Connection.js";
import { StateManager } from "./StateManager.js";
import { CounterService } from "./CounterService.js";
import { UserService } from "./UserService.js";
import { ChatService } from "./ChatService.js";
import fetch from "../Helpers/fetch.js";
import Body from "../Helpers/Body.js";

const WaitingState = (await fetch("https://web/api/state/0", { "method": "GET" })).json;
const StartingState = (await fetch("https://web/api/state/1", { "method": "GET" })).json;

export class GameService {
	/**
   * @type {NodeJS.Timeout[]}
   */
	timeouts = [];

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

	static async isAuthor(socket, gameId) {
		const game = await GameService.getGame(gameId);
		if (!game) return false;

		const members = JSON.parse(await client.get("game:" + gameId + ":members")) ?? [];
		const user = UserService.getUserBySocket(socket, members);

		if (!user) return false;

		return game.owner === user.user_id;
	}

	async startGame(channel, game, members, socket) {
		game.is_started = true;
		const gameId = channel.split(".")[1];
		await this.setGame(gameId, game);

		this.StateManager.setState({
			status: StartingState.state,
			startTimestamp: Date.now(),
			counterDuration: StartingState.duration
		}, channel);

		if (process.env.APP_DEBUG) {
			console.info(`[${new Date().toISOString()}] - Starting game id ${channel.split(".")[1]}\n`);
		}

		this.timeouts.push(setTimeout(async () => {
			await this.roleManagement(channel, members);
		}, 6000));

		this.timeouts.push(setTimeout(async () => {
			await this.counterService.cycle(channel, socket);
		}, 11000));
	}

	async stopGameLaunch(channel) {
		if (this.timeouts.length > 0) {
			this.timeouts.forEach(clearTimeout);
		}

		await this.StateManager.setState({
			status: WaitingState.state,
			startTimestamp: Date.now(),
			counterDuration: WaitingState.duration
		}, channel);
	}

	async roleManagement(channel, members) {
		const gameId = channel.split(".")[1];
		const params = Body.make({
			gameId
		});

		await fetch("https://web/api/roles/assign", { method: "POST", body: params });
		const game = await GameService.getGame(gameId);

		for (const member of members) {
			const user = UserService.getUserBySocket(member.socketId, members);
			const roleId = game.assigned_roles[user.user_id];
			let role = await fetch(`https://web/api/roles/get/${roleId}`, { method: "GET" });

			role = role.json.role;
			this.io.to(member.socketId).emit("game.role-assign", channel, game.assigned_roles[user.user_id]);
			ChatService.info(this.io, channel, `Votre role est : ${role.display_name}`, member.socketId);
		}
	}

	async getRolesCount(gameId) {
		const game = await GameService.getGame(gameId);
		if (!game) return;
		let count = 0;

		for (const role in game.roles) {
			count += game.roles[role];
		}
		return count;
	}
}
