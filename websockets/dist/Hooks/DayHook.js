import fetch from "../Helpers/fetch.js";
import { gameId } from "../Helpers/Functions.js";
import { client } from "../Redis/Connection.js";
import { InteractionService } from "../Services/InteractionService.js";
export default {
    identifier: 6,
    async before(io, channel) {
        const id = gameId(channel);
        const body = {
            gameId: id
        };
        const baseUrl = `${process.env.API_URL}/game`;
        const interactions = JSON.parse(await client.get(`game:${id}:interactions`));
        const interaction = interactions.find((interactionListItem) => interactionListItem.type === "angel");
        if (interaction) {
            const res = await fetch(`${baseUrl}/interactions/status`, "POST", { gameId: id, type: "angel" });
            if (res.json === true) {
                await InteractionService.closeInteraction(io, channel, "angel");
            }
        }
        const game = JSON.parse(await client.get(`game:${id}`));
        const state = JSON.parse(await client.get(`game:${id}:state`));
        if ("bitten" in game && (state.round ?? 0) - game["bitten"].round === 1) {
            await fetch(`${baseUrl}/kill`, "POST", {
                userId: game["bitten"].target,
                gameId: id,
                context: 'bitten'
            });
        }
        await fetch(`${baseUrl}/chat/lock/false`, "POST", body);
        await fetch(`${baseUrl}/message/deaths`, "POST", body);
        const res = await fetch(`${baseUrl}/end/check`, "POST", body);
        if (res.status === 204) {
            await fetch(`${baseUrl}/end`, "POST", body);
            return true;
        }
        return false;
    }
};
