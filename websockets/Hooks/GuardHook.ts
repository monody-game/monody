import { InteractionService } from "../Services/InteractionService.js";
import { Server } from "socket.io";

export default {
	identifier: 16,
	async before(io: Server, channel: string) {
		await InteractionService.openInteraction(io, channel, "guard");

		return false;
	},
	async after(io: Server, channel: string) {
		await InteractionService.closeInteraction(io, channel, "guard");

		return false;
	}
};
