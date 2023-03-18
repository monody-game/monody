import { client } from "./Connection.js";
import { log } from "../Logger.js";
export class RedisSubscriber {
    sub;
    constructor() {
        this.sub = client.duplicate();
        this.sub.connect();
    }
    async subscribe(callback) {
        await this.sub.pSubscribe("*", async (message, channel) => {
            if (process.env.APP_DEBUG) {
                log("Api emitted an event !");
                log("Channel: " + channel);
                log("Event: " + JSON.parse(message).event);
            }
            return await callback(channel, JSON.parse(message));
        });
    }
    async unsubscribe() {
        await this.sub.pUnsubscribe("*");
        await this.sub.disconnect();
    }
}
