import { InteractionService } from "../Services/InteractionService.js";
import {Server} from "socket.io";

export default {
	identifier: 12,
	async before(io: Server, channel: string) {
		await InteractionService.openInteraction(io, channel, "mayor");

		return false;
	},
	async after(io: Server, channel: string) {
		await InteractionService.closeInteraction(io, channel, "mayor");

		return false;
	}
};
