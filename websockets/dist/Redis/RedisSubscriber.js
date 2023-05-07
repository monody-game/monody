import { client } from "./Connection.js";
import { error, log } from "../Logger.js";
export class RedisSubscriber {
    sub;
    constructor() {
        this.sub = client.duplicate();
        this.sub.connect();
    }
    async subscribe(callback) {
        await this.sub.pSubscribe("*", async (message, channel) => {
            try {
                const event = JSON.parse(message);
                if (process.env.APP_DEBUG) {
                    log("Api emitted an event !");
                    log("Channel: " + channel);
                    log("Event: " + event.event);
                }
                return await callback(channel, event);
            }
            catch (e) {
                error(e);
            }
        });
    }
    async unsubscribe() {
        await this.sub.pUnsubscribe("*");
        await this.sub.disconnect();
    }
}
