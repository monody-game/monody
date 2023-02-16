import fetch from "../Helpers/fetch.js";
import Body from "../Helpers/Body.js";
import { error, log, warn } from "../Logger.js";

export class PrivateChannel {
	authenticate(socket, data) {
		const options = {
			form: { channel_name: data.channel },
		};

		if (process.env.APP_DEBUG) {
			log(`Sending auth request to: "${process.env.APP_URL}"`);
		}

		return this.serverRequest(socket, options);
	}

	async serverRequest(socket, options) {
		let response;

		const params = Body.make({
			channel_name: options.form.channel_name
		});

		if (process.env.APP_ENV === "local") {
			response = await fetch("https://web/broadcasting/auth", {
				method: "POST",
				body: params,
				headers: options
			}, socket);
		} else {
			response = await fetch(`${process.env.APP_URL}/broadcasting/auth`, {
				method: "POST",
				body: params,
				headers: options
			}, socket);
		}

		const status = response.status;
		response = response.text;

		if (status !== 200) {
			if (process.env.APP_DEBUG) {
				warn(`${socket.id} could not be authenticated to ${options.form.channel_name}`);
				warn(response);
			}

			error("Client cannot be authenticated, got HTTP status " + status);

			throw new Error();
		}

		if (process.env.APP_DEBUG) {
			log(`${socket.id} authenticated for: ${options.form.channel_name}`);
		}

		return response;
	}
}
