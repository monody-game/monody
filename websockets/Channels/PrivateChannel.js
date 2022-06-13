const fetch = require("../Helpers/fetch");

module.exports.PrivateChannel = class {
	authenticate(socket, data) {
		const options = {
			form: { channel_name: data.channel },
		};

		if (process.env.APP_DEBUG) {
			console.info(`[${new Date().toISOString()}] - Sending auth request to: "https://web"\n`);
		}

		return this.serverRequest(socket, options);
	}

	async serverRequest(socket, options) {
		let response;
		let status = 0;

		try {
			const params = new URLSearchParams();

			params.set("channel_name", options.form.channel_name);

			response = await fetch("https://web/broadcasting/auth", {
				method: "POST",
				body: params,
				headers: options
			}, socket);

			status = response.status;
			response = response.text;

			if (process.env.APP_DEBUG) {
				console.info(`[${new Date().toISOString()}] - ${socket.id} authenticated for: ${options.form.channel_name}`);
			}

			return response;
		} catch (error) {
			if (error) {
				if (process.env.APP_DEBUG) {
					console.error(`[${new Date().toISOString()}] - Error authenticating ${socket.id} for ${options.form.channel_name}`);
					console.error(error);
				}
				throw ({ reason: "Error sending authentication request.", status });
			} else if (status !== 200) {
				if (process.env.APP_DEBUG) {
					console.warn(`[${new Date().toISOString()}] - ${socket.id} could not be authenticated to ${options.form.channel_name}`);
					console.error(response);
				}

				throw ({ reason: "Client can not be authenticated, got HTTP status " + status, status });
			}
		}
	}
};
