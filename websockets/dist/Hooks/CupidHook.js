import { InteractionService } from "../Services/InteractionService.js";
export default {
    identifier: 15,
    async before(io, channel) {
        await InteractionService.openInteraction(io, channel, "cupid");
        return false;
    },
    async after(io, channel) {
        await InteractionService.closeInteraction(io, channel, "cupid");
        return false;
    }
};
