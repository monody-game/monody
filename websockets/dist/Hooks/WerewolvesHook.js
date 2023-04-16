import { InteractionService } from "../Services/InteractionService.js";
import fetch from "../Helpers/fetch.js";
import { gameId } from "../Helpers/Functions.js";
const baseURL = `${process.env.API_URL}/game`;
export default {
    identifier: 3,
    async before(io, channel) {
        await InteractionService.openInteraction(io, channel, "werewolves");
        await fetch(`${baseURL}/chat/lock/false`, "POST", {
            gameId: gameId(channel),
            team: "2"
        });
        return false;
    },
    async after(io, channel) {
        await InteractionService.closeInteraction(io, channel, "werewolves");
        await fetch(`${baseURL}/chat/lock/true`, "POST", {
            gameId: gameId(channel),
            team: "2"
        });
        return false;
    },
};
