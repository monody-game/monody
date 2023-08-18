import { InteractionService } from "../Services/InteractionService.js";
import { Server } from "socket.io";

export default {
	identifier: 13,
	async before(io: Server, channel: string) {
		await InteractionService.openInteraction(io, channel, "surly_werewolf");

		return false;
	},
	async after(io: Server, channel: string) {
		await InteractionService.closeInteraction(
			io,
			channel,
			"surly_werewolf",
		);

		return false;
	},
};
