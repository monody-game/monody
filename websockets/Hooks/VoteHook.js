import { InteractionService } from "../Services/InteractionService.js";

export default {
	identifier: 7,
	async before(io, channel) {
		await InteractionService.openInteraction(io, channel, "vote");
	},
	async after(io, channel) {
		await InteractionService.closeInteraction(io, channel, "vote");
	},
};
