import { client } from "./Connection.js";
import { info } from "../Logger.js";

export class RedisSubscriber {
	constructor() {
		this.sub = client.duplicate();

		this.sub.connect();
	}

	async subscribe(callback) {
		await this.sub.pSubscribe("*", async (message, channel) => {
			if (process.env.APP_DEBUG) {
				info("Api emitted an event !");
				info("Channel: " + channel);
				info("Event: " + JSON.parse(message).event);
			}
			return await callback(channel, JSON.parse(message));
		});
	}

	async unsubscribe() {
		await this.sub.pUnsubscribe("*");
		await this.sub.disconnect();
	}
}
