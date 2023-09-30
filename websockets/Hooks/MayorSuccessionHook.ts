import { InteractionService } from "../Services/InteractionService.js";
import { Server } from "socket.io";

export default {
	identifier: 20,
	async before(io: Server, channel: string) {
		await InteractionService.openInteraction(io, channel, "mayor_succession");

		return false;
	},
	async after(io: Server, channel: string) {
		await InteractionService.closeInteraction(io, channel, "mayor_succession");

		return false;
	},
};
