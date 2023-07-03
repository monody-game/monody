import { InteractionService } from "../Services/InteractionService.js";
import fetch from "../Helpers/fetch.js";
import { gameId } from "../Helpers/Functions.js";
const baseUrl = `${process.env.API_URL}/game`;
const body = (channel) => {
    return { gameId: gameId(channel) };
};
export default {
    identifier: 17,
    async before(io, channel) {
        await fetch(`${baseUrl}/message/deaths`, "POST", body(channel));
        await InteractionService.openInteraction(io, channel, "hunter");
        return false;
    },
    async after(io, channel) {
        await InteractionService.closeInteraction(io, channel, "hunter");
        await fetch(`${baseUrl}/message/deaths`, "POST", body(channel));
        const res = await fetch(`${baseUrl}/end/check`, "POST", body);
        if (res.status === 204) {
            await fetch(`${baseUrl}/end`, "POST", body);
            return true;
        }
        return false;
    },
};
