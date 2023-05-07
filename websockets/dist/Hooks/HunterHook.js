import { InteractionService } from "../Services/InteractionService.js";
import fetch from "../Helpers/fetch.js";
import { gameId } from "../Helpers/Functions.js";
export default {
    identifier: 17,
    async before(io, channel) {
        await InteractionService.openInteraction(io, channel, "hunter");
        return false;
    },
    async after(io, channel) {
        await InteractionService.closeInteraction(io, channel, "hunter");
        const baseUrl = `${process.env.API_URL}/game`;
        const body = { gameId: gameId(channel) };
        await fetch(`${baseUrl}/message/deaths`, "POST", body);
        return false;
    }
};
