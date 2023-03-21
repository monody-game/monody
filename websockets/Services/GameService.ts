import { client } from "../Redis/Connection.js";
import { StateManager } from "./StateManager.js";
import { CounterService } from "./CounterService.js";
import { UserService } from "./UserService.js";
import fetch from "../Helpers/fetch.js";
import { gameId } from "../Helpers/Functions.js";
import { success } from "../Logger.js";
import { EventEmitter } from "node:events";
import {Server, Socket} from "socket.io";

const WaitingState = (await fetch(`${process.env.API_URL}/state/0`)).json;

type Member = {
	socketId?: string
	user_id: string
	user_info: object
}

type MemberList = Member[]

export class GameService {
	private emitter: EventEmitter
	private stateManager: StateManager
	private counterService: CounterService

	constructor(io: Server, emitter: EventEmitter) {
		this.emitter = emitter;
		this.stateManager = new StateManager(io, emitter);
		this.counterService = new CounterService(io, emitter);

		this.emitter.removeAllListeners("game.start");

		this.emitter.on('game.start', async (data) => {
			await this.startGame(`presence-game.${data.game.id}`, data.game, io)
		})
	}

	static async getGame(id: string) {
		return JSON.parse(await client.get("game:" + id) as string);
	}

	static async exists(id: string) {
		return await client.exists("game:" + id);
	}

	static async getMembers(id: string): Promise<MemberList> {
		return JSON.parse(await client.get(`game:${id}:members`) as string);
	}

	async setGame(id: string, data: object) {
		await client.set("game:" + id, JSON.stringify(data));
	}

	async startGame(channel: string, game: {[key: string]: boolean|object|string}, socket: Socket|Server) {
		const shared = JSON.parse(await client.get("bot:game:shared") ?? "{}");
		delete shared[game.id as string]
		await client.set("bot:game:shared", JSON.stringify(shared));

		game.is_started = true;
		const id = gameId(channel);
		await this.setGame(id, game);

		await this.counterService.cycle(channel, socket);

		if (process.env.APP_DEBUG) {
			success(`Starting game with id ${id}`);
		}
	}

	async stopGameLaunch(channel: string) {
		this.emitter.emit("time.halt", gameId(channel));
		await this.stateManager.setState({
			status: WaitingState.state,
			startTimestamp: Date.now(),
			counterDuration: WaitingState.duration,
			round: 0
		}, channel);
	}

	static async roleManagement(io: Server, channel: string) {
		const id = gameId(channel);
		const members = await GameService.getMembers(id);

		await fetch(`${process.env.API_URL}/roles/assign`, "POST", { gameId: id });
		const game = await GameService.getGame(id);

		for (const member of members) {
			if(!member.socketId) continue;

			const user = UserService.getUserBySocket(member.socketId, members);
			const roleId = game.assigned_roles[user.user_id];
			let role = await fetch(`${process.env.API_URL}/roles/get/${roleId}`);

			role = role.json.role;
			io.to(member.socketId).emit("game.role-assign", channel, game.assigned_roles[user.user_id]);
		}
	}

	async getRolesCount(id: string) {
		const game = await GameService.getGame(id);
		let count = 0;

		if (!game) return count;

		for (const role in game.roles) {
			count += game.roles[role];
		}

		return count;
	}
}

export { MemberList, Member }
