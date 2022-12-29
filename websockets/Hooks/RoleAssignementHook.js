import { GameService } from "../Services/GameService.js";

export default {
	identifier: 9,
	async before(io, channel) {
		await GameService.roleManagement(io, channel);
	}
};
