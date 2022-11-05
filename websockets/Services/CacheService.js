import { client } from "../Redis/Connection.js";

export class CacheService {
	static async process(key, value) {
		if (await this.exists(key)) {
			if (this.different(key, value)) {
				await this.set(key, value);

				return value;
			}

			return await this.get(key);
		}

		await this.set(key, value);

		return value;
	}

	static async set(key, value) {
		await client.set(`cache:ws:${key}`, JSON.stringify(value));
	}

	static async get(key) {
		return JSON.parse(await client.get(`cache:ws:${key}`));
	}

	static different(key, value) {
		return this.get(key) !== value;
	}

	static async exists(key) {
		return await client.exists(`cache:ws:${key}`);
	}
}
