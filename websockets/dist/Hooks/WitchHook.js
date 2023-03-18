import { InteractionService } from "../Services/InteractionService.js";
export default {
    identifier: 4,
    async before(io, channel) {
        await InteractionService.openInteraction(io, channel, "witch");
        return false;
    },
    async after(io, channel) {
        await InteractionService.closeInteraction(io, channel, "witch");
        return false;
    }
};
