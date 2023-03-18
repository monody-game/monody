import { InteractionService } from "../Services/InteractionService.js";
export default {
    identifier: 11,
    async before(io, channel) {
        await InteractionService.openInteraction(io, channel, "white_werewolf");
        return false;
    },
    async after(io, channel) {
        await InteractionService.closeInteraction(io, channel, "white_werewolf");
        return false;
    }
};
