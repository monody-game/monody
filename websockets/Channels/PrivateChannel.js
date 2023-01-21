import fetch from "../Helpers/fetch.js";
import Body from "../Helpers/Body.js";
import { error, info } from "../Logger.js";
import { warn } from "vue";

export class PrivateChannel {
	authenticate(socket, data) {
		const options = {
			form: { channel_name: data.channel },
		};

		if (process.env.APP_DEBUG) {
			info(`Sending auth request to: "${process.env.APP_URL}"`);
		}

		return this.serverRequest(socket, options);
	}

	async serverRequest(socket, options) {
		let response;
		let status = 0;

		try {
			const params = Body.make({
				channel_name: options.form.channel_name
			});

			response = await fetch(`${process.env.APP_URL}/broadcasting/auth`, {
				method: "POST",
				body: params,
				headers: options
			}, socket);

			status = response.status;
			response = response.text;

			if (process.env.APP_DEBUG) {
				info(`${socket.id} authenticated for: ${options.form.channel_name}`);
			}

			return response;
		} catch (e) {
			if (e) {
				if (process.env.APP_DEBUG) {
					error(`Error authenticating ${socket.id} for ${options.form.channel_name}`);
					error(e);
				}

				error("Error sending authentication request. Got HTTP status " + status);
			} else if (status !== 200) {
				if (process.env.APP_DEBUG) {
					warn(`${socket.id} could not be authenticated to ${options.form.channel_name}`);
					warn(response);
				}

				error("Client cannot be authenticated, got HTTP status " + status);
			}
		}
	}
}
