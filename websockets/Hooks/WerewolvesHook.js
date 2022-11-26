import { InteractionService } from "../Services/InteractionService.js";

export default {
	identifier: 3,
	async before(io, channel) {
		await InteractionService.openInteraction(io, channel, "werewolves");
	},
	async after(io, channel) {
		await InteractionService.closeInteraction(io, channel, "werewolves");
	},
};
