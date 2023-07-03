import { InteractionService } from "../Services/InteractionService.js";
import { Server } from "socket.io";

export default {
	identifier: 14,
	async before(io: Server, channel: string) {
		await InteractionService.openInteraction(io, channel, "parasite");

		return false;
	},
	async after(io: Server, channel: string) {
		await InteractionService.closeInteraction(io, channel, "parasite");

		return false;
	},
};
