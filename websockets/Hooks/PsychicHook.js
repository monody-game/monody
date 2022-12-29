import { InteractionService } from "../Services/InteractionService.js";

export default {
	identifier: 5,
	async before(io, channel) {
		await InteractionService.openInteraction(io, channel, "psychic");

		return false;
	},
	async after(io, channel) {
		await InteractionService.closeInteraction(io, channel, "psychic");

		return false;
	}
};
