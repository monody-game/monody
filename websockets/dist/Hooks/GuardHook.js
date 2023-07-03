import { InteractionService } from "../Services/InteractionService.js";
export default {
    identifier: 16,
    async before(io, channel) {
        await InteractionService.openInteraction(io, channel, "guard");
        return false;
    },
    async after(io, channel) {
        await InteractionService.closeInteraction(io, channel, "guard");
        return false;
    },
};
