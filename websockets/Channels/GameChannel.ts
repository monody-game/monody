import { StateManager } from "../Services/StateManager.js";
import { client } from "../Redis/Connection.js";
import {GameService, Member, MemberList} from "../Services/GameService.js";
import fetch from "../Helpers/fetch.js";
import { gameId } from "../Helpers/Functions.js";
import { log } from "../Logger.js";
import {Server, Socket} from "socket.io";
import {EventEmitter} from "node:events";

const StartingState = (await fetch(`${process.env.API_URL}/state/1`, "GET")).json;
const EndState = (await fetch(`${process.env.API_URL}/state/8`, "GET")).json;

export class GameChannel {
	private io: Server;
	private gameService: GameService;
	private stateManager: StateManager;
	private emitter: EventEmitter

	constructor(io: Server, emitter: EventEmitter) {
		this.io = io;
		this.gameService = new GameService(io, emitter);
		this.stateManager = new StateManager(io, emitter);
		this.emitter = emitter
	}

	async getMembers(channel: string): Promise<MemberList> {
		const members = JSON.parse(await client.get(`game:${gameId(channel)}:members`) as string);
		if (!members) return [];
		return members;
	}

	async isMember(channel: string, member: Member) {
		let members = await this.getMembers(channel);
		members = await this.removeInactive(channel, members);
		const search = members.filter(m => m.user_id === member.user_id);
		return search && search.length > 0;
	}

	async removeInactive(channel: string, members: MemberList) {
		const clients = await this.io.in(channel).fetchSockets();
		members = members.filter(member => {
			if(member.socketId) return clients.filter(client => client.id === member.socketId).length >= 0;
			return false;
		});
		await client.set(`game:${gameId(channel)}:members`, JSON.stringify(members));
		return members;
	}

	async join(socket: Socket, channel: string, member: Member) {
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

		await fetch(`${process.env.API_URL}/game/join`, "POST", {
			gameId: id,
			userId: member.user_id
		});

		const count = await this.gameService.getRolesCount(id);
		const game = await GameService.getGame(id);

		if (members.length === count && game.is_started === false) {
			const canStart = await fetch(`${process.env.API_URL}/game/start/check`, 'POST', {
				gameId: game.id
			})

			if (canStart.ok) {
				await this.gameService.startGame(channel, game, socket);

				const list = await fetch(`${process.env.API_URL}/game/list/*`, "GET");

				socket.broadcast.to("home").volatile.emit("game-list.update", "home", {
					data: list.json.data
				});
			}
		}
	}

	async leave(socket: Socket, channel: string) {
		const id = gameId(channel);
		const game = await GameService.getGame(id);
		let members = await this.getMembers(channel);
		members = members || [];

		if (!game) return;

		const state = await this.stateManager.getState(id);
		if (!state) return;

		if (state.status === StartingState.data.state.id) {
			log(`Stopping starting state of game ${id}`);
			await this.gameService.stopGameLaunch(channel);
			game.is_started = false;
			await this.gameService.setGame(id, game);
		}

		const member = members.find(m => m.socketId === socket.id);
		if (!member) return;
		members = members.filter(m => m.socketId !== member.socketId);

		await fetch(`${process.env.API_URL}/game/leave`, "POST", {
			userId: member.user_id
		});

		if (members.length === 0) {
			await this.onLeave(channel, member);
			await this.onDelete(id);
		} else {
			await client.set(`game:${id}:members`, JSON.stringify(members));
			game.users = game.users.filter((userId: string) => userId !== member.user_id);
			await this.gameService.setGame(id, game);

			const isMember = await this.isMember(channel, member);

			if (!isMember && member) {
				delete member.socketId;
				await this.onLeave(channel, member);
			}
		}
	}

	async onJoin(socket: Socket, channel: string, member: Member) {
		if(!member.socketId) return

		socket.broadcast.to(channel).emit("presence:joining", channel, member);

		const gameData = await fetch(`${process.env.API_URL}/game/data/${gameId(channel)}/${member.user_id}`);

		this.io.to(member.socketId).emit("game.data", channel, { data: { payload: gameData.json.data.game } });
	}

	async onLeave(channel: string, member: Member) {
		const id = gameId(channel);
		const game = JSON.parse(await client.get(`game:${id}`) as string)
		const state = await this.stateManager.getState(id);

		if(!game.is_started || state === EndState.data.state.id) {
			this.io.to(channel).emit("presence:leaving", channel, member);
		} else {
			this.io.to(channel).emit("list.disconnect", channel, member)
		}

		setTimeout(async () => {
			const isMember = await this.isMember(channel, member);

			if (isMember) {
				return
			}

			await fetch(`${process.env.API_URL}/game/kill`, "POST", {
				userId: member.user_id,
				gameId: id,
				context: 'disconnect',
				instant: true
			});

			const canEnd = await fetch(`${process.env.API_URL}/game/end/check`, "POST", { gameId: id });

			if (canEnd.status === 204) {
				await fetch(`${process.env.API_URL}/game/end`, "POST", { gameId: id });
				this.emitter.emit("time.halt")
				await this.stateManager.setState({
					status: EndState.data.state.id,
					round: state.round,
					counterDuration: EndState.data.state.duration,
					startTimestamp: Date.now()
				}, channel)
			}
		}, 10_000)
	}

	async onDelete(id: string) {
		await fetch(`${process.env.API_URL}/game`, "DELETE", { gameId: id });

		this.io.to('bot.private').volatile.emit('game.share.clear', 'bot.private')

		log(`Deleting game with id: ${id}`);
	}

	async onSubscribed(socket: Socket, channel: string, members: MemberList) {
		this.io.to(socket.id).emit("presence:subscribed", channel, members);
	}
}
