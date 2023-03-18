import { InteractionService } from "../Services/InteractionService.js";
import fetch from "../Helpers/fetch.js";
import { gameId } from "../Helpers/Functions.js";
import { client } from "../Redis/Connection.js";
export default {
    identifier: 7,
    async before(io, channel) {
        await InteractionService.openInteraction(io, channel, "vote");
        return false;
    },
    async after(io, channel) {
        await InteractionService.closeInteraction(io, channel, "vote");
        const id = gameId(channel);
        const interactions = JSON.parse(await client.get(`game:${id}:interactions`));
        const interaction = interactions.find((interactionListItem) => interactionListItem.type === "angel");
        if (interaction) {
            await InteractionService.closeInteraction(io, channel, "angel");
        }
        const body = { gameId: gameId(channel) };
        const baseURL = `${process.env.API_URL}/game`;
        await fetch(`${baseURL}/message/deaths`, "POST", body);
        const res = await fetch(`${baseURL}/end/check`, "POST", body);
        if (res.status === 204) {
            await fetch(`${baseURL}/end`, "POST", body);
            return true;
        }
        return false;
    },
};
