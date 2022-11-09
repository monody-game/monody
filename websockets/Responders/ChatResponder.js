import { BaseResponder } from "./BaseResponder.js";
import { client } from "../Redis/Connection.js";

export default class ChatResponder extends BaseResponder {
	constructor() {
		super();
		this.respondTo = [
			/chat(\.\w*)+/
		];
	}

	async emit(socket, data) {
		const game = JSON.parse(await client.get("game:" + data.channel.split(".")[1]));
		const members = await this.getMembers(data.channel);
		switch (data.event) {
		case "client-chat.werewolf.send":
			data.data.author = members.find(member => member.user_id === data.data.author).user_info;

			members.forEach(member => {
				if (game.werewolves.indexOf(parseInt(member.user_id)) >= 0) {
					socket.to(member.socketId).emit("chat.werewolf", data.channel, data.data);
				}
			});
			break;
		default:
			console.warn("Unknown event: " + data.event);
			break;
		}
	}

	async getMembers(channel) {
		const members = JSON.parse(await client.get(`game:${channel.split(".")[1]}:members`));
		if (!members) return [];
		return members;
	}
}
