import { client } from "../Redis/Connection.js";
import { StateManager } from "./StateManager.js";
import { CounterService } from "./CounterService.js";
import { UserService } from "./UserService.js";
import fetch from "../Helpers/fetch.js";
import Body from "../Helpers/Body.js";
import { gameId } from "../Helpers/Functions.js";
import { success } from "../Logger.js";

const WaitingState = (await fetch(`${process.env.API_URL}/state/0`, { "method": "GET" })).json;

export class GameService {
	constructor(io, emitter) {
		this.io = io;
		this.emitter = emitter;
		this.StateManager = new StateManager(io, emitter);
		this.counterService = new CounterService(io, emitter);
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

	async startGame(channel, game, members, socket) {
		game.is_started = true;
		const id = gameId(channel);
		await this.setGame(id, game);

		await this.counterService.cycle(channel, socket);

		if (process.env.APP_DEBUG) {
			success(`Starting game with id ${id}`);
		}
	}

	async stopGameLaunch(channel) {
		this.emitter.emit("time.halt", gameId(channel));
		await this.StateManager.setState({
			status: WaitingState.state,
			startTimestamp: Date.now(),
			counterDuration: WaitingState.duration
		}, channel);
	}

	static async roleManagement(io, channel) {
		const id = gameId(channel);
		const params = Body.make({
			gameId: id
		});
		const members = await GameService.getMembers(id);

		await fetch(`${process.env.API_URL}/roles/assign`, { method: "POST", body: params });
		const game = await GameService.getGame(id);

		for (const member of members) {
			const user = UserService.getUserBySocket(member.socketId, members);
			const roleId = game.assigned_roles[user.user_id];
			let role = await fetch(`${process.env.API_URL}/roles/get/${roleId}`, { method: "GET" });

			role = role.json.role;
			io.to(member.socketId).emit("game.role-assign", channel, game.assigned_roles[user.user_id]);
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
}
