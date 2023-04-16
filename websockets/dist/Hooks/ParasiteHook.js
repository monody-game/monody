import { InteractionService } from "../Services/InteractionService.js";
export default {
    identifier: 14,
    async before(io, channel) {
        await InteractionService.openInteraction(io, channel, "parasite");
        return false;
    },
    async after(io, channel) {
        await InteractionService.closeInteraction(io, channel, "parasite");
        return false;
    }
};
