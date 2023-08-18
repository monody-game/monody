import { client } from "./Connection.js";
import { error, info } from "../Logger.js";

export class RedisSubscriber {
	private sub: typeof client;

	constructor() {
		this.sub = client.duplicate();

		this.sub.connect();
	}

	async subscribe(callback: Function) {
		await this.sub.pSubscribe("*", async (message, channel) => {
			try {
				const event = JSON.parse(message);

				if (process.env.APP_DEBUG) {
					info("Api emitted an event !");
					info("Channel: " + channel);
					info("Event: " + event.event);
				}

				return await callback(channel, event);
			} catch (e) {
				error(e);
			}
		});
	}

	async unsubscribe() {
		await this.sub.pUnsubscribe("*");
		await this.sub.disconnect();
	}
}
