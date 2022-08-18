import { client } from "../Redis/Connection.js";
const RoleManager = (await import("./RoleManager.js")).default;
import { StateManager } from "./StateManager.js";
const CounterService = (await import("./CounterService.js")).default;
const UserService = (await import("./UserService.js")).default;
const StartingState = (await import("../Rounds/States/StartingState.js")).default;
const WaitingState = (await import("../Rounds/States/WaitingState.js")).default;
const ChatService = (await import("./ChatService.js")).default;
const fetch = (await import("../Helpers/fetch.js")).default;
const werewolves = [1];

export default class GameService {
	/**
   * @type {NodeJS.Timeout[]}
   */
	timeouts = [];

	constructor(io) {
		this.io = io;
		this.StateManager = new StateManager(io);
		this.counterService = new CounterService(io);
	}

	async getGame(id) {
		return JSON.parse(await client.get("game:" + id));
	}

	async setGame(id, data) {
		await client.set("game:" + id, JSON.stringify(data));
	}

	async isAuthor(socket, gameId) {
		const game = await this.getGame(gameId);
		if (!game) return false;

		const members = JSON.parse(await client.get("game:" + gameId + ":members")) ?? [];
		const user = await UserService.getUserBySocket(socket, members);

		if (!user) return false;

		return game.owner === user.user_id;
	}

	async startGame(channel, game, members, socket) {
		game.is_started = true;
		const gameId = channel.split(".")[1];
		await this.setGame(gameId, game);

		this.StateManager.setState({
			status: StartingState.identifier,
			startTimestamp: Date.now(),
			counterDuration: StartingState.duration
		}, channel);

		if (process.env.APP_DEBUG) {
			console.info(`[${new Date().toISOString()}] - Starting game id ${channel.split(".")[1]}\n`);
		}

		this.timeouts.push(setTimeout(async () => {
			await this.roleManagement(game, channel, members, socket);
		}, 6000));

		this.timeouts.push(setTimeout(async () => {
			await this.counterService.cycle(channel);
		}, 11000));
	}

	async stopGameLaunch(channel) {
		if (this.timeouts.length > 0) {
			this.timeouts.forEach(clearTimeout);
		}

		await this.StateManager.setState({
			status: WaitingState.identifier,
			startTimestamp: Date.now(),
			counterDuration: WaitingState.duration
		}, channel);
	}

	async roleManagement(game, channel, members, socket) {
		const gameWerewolves = [];
		game.assigned_roles = RoleManager.assign(game.roles, members);

		Object.keys(game.assigned_roles).forEach(member => {
			if (werewolves.indexOf(game.assigned_roles[member]) >= 0) gameWerewolves.push(parseInt(member));
		});
		game.werewolves = gameWerewolves;

		for (const member of members) {
			const user = await UserService.getUserBySocket(member.socketId, members);
			const roleId = game.assigned_roles[user.user_id];
			let role = await fetch(`https://web/api/roles/get/${roleId}`, { method: "GET" }, socket);

			role = role.json.role;
			this.io.to(member.socketId).emit("game.role-assign", channel, game.assigned_roles[user.user_id]);
			ChatService.info(this.io, channel, `Votre role est : ${role.display_name}`, member.socketId);
		}

		await this.setGame(channel.split(".")[1], game);
	}

	async getRolesCount(gameId) {
		const game = await this.getGame(gameId);
		if (!game) return;
		let count = 0;

		for (const role in game.roles) {
			count += game.roles[role];
		}
		return count;
	}
}
