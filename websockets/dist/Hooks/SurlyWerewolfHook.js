import { InteractionService } from "../Services/InteractionService.js";
export default {
    identifier: 13,
    async before(io, channel) {
        await InteractionService.openInteraction(io, channel, "surly_werewolf");
        return false;
    },
    async after(io, channel) {
        await InteractionService.closeInteraction(io, channel, "surly_werewolf");
        return false;
    },
};
