import { InteractionService } from "../Services/InteractionService.js";
export default {
    identifier: 10,
    async before(io, channel) {
        await InteractionService.openInteraction(io, channel, "infected_werewolf");
        return false;
    },
    async after(io, channel) {
        await InteractionService.closeInteraction(io, channel, "infected_werewolf");
        return false;
    },
};
