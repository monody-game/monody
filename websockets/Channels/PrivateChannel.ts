import fetch from "../Helpers/fetch.js";
import {error, info, warn} from "../Logger.js";
import {Socket} from "socket.io";
import {DataPayload} from "../IoServer.js";

export class PrivateChannel {
	authenticate(socket: Socket, data: DataPayload) {
		if (process.env.APP_DEBUG) {
			info(`Sending auth request to: "${process.env.APP_URL}"`);
		}

		return this.serverRequest(socket, {
			form: { channel_name: data.channel },
		});
	}

	async serverRequest(socket: Socket, options: {[key: string]: any}) {
		let response;

		const params = {
			channel_name: options.form.channel_name,
			socket_id: socket.id
		};

		if (process.env.APP_ENV === "local") {
			response = await fetch("https://web/broadcasting/auth", "POST", params, socket);
		} else {
			response = await fetch(`${process.env.APP_URL}/broadcasting/auth`, "POST", params,socket);
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
			info(`${socket.id} authenticated for: ${options.form.channel_name}`);
		}

		return response;
	}
}
