import { GameService } from "../Services/GameService.js";
import { InteractionService } from "../Services/InteractionService.js";
import { gameId } from "../Helpers/Functions.js";
import { client } from "../Redis/Connection.js";
export default {
    identifier: 9,
    async before(io, channel) {
        await GameService.roleManagement(io, channel);
        const game = JSON.parse(await client.get(`game:${gameId(channel)}`));
        // If there is an angel in the game
        if (Object.keys(game.roles).includes("9")) {
            await InteractionService.openInteraction(io, channel, "angel");
        }
    }
};
