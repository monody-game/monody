import fetch from "../Helpers/fetch.js";
import { gameId } from "../Helpers/Functions.js";
export default {
    identifier: 2,
    async before(io, channel) {
        await fetch(`${process.env.API_URL}/game/chat/lock`, "POST", { gameId: gameId(channel) });
        return false;
    }
};
