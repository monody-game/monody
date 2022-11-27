import { client } from "./Connection.js";

export class RedisSubscriber {
	constructor() {
		this.sub = client.duplicate();

		this.sub.connect();
	}

	async subscribe(callback) {
		await this.sub.pSubscribe("*", async (message, channel) => {
			if (process.env.APP_DEBUG) {
				console.info("Channel: " + channel);
				console.info("Event: " + JSON.parse(message).event);
			}
			return await callback(channel, JSON.parse(message));
		});
	}

	async unsubscribe() {
		await this.sub.pUnsubscribe("*");
		await this.sub.disconnect();
	}
}
