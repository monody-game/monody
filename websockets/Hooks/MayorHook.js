import { InteractionService } from "../Services/InteractionService.js";

export default {
	identifier: 12,
	async before(io, channel) {
		await InteractionService.openInteraction(io, channel, "mayor");

		return false;
	},
	async after(io, channel) {
		await InteractionService.closeInteraction(io, channel, "mayor");

		return false;
	}
};
