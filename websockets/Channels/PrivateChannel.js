const fetch = require("node-fetch");

module.exports.PrivateChannel = class {
	authenticate(socket, data) {
		const options = {
			form: { channel_name: data.channel },
			headers: (data.auth && data.auth.headers) ? data.auth.headers : {},
			rejectUnauthorized: false,
			credentials: "include",
		};

		if (process.env.APP_DEBUG) {
			console.info(`[${new Date().toISOString()}] - Sending auth request to: "http://web"\n`);
		}

		return this.serverRequest(socket, options);
	}

	hasMatchingHost(referer, host) {
		return (referer.hostname && referer.hostname.substring(referer.hostname.indexOf(".")) === host) ||
      `${referer.protocol}//${referer.host}` === host ||
      referer.host === host;
	}

	async serverRequest(socket, options) {
		let body;
		let response;
		let status;

		try {
			const params = new URLSearchParams();

			params.set("channel_name", options.form.channel_name);

			response = await fetch("http://web/broadcasting/auth", {
				method: "POST",
				headers: this.prepareHeaders(socket, options),
				body: params,
			});

			status = response.statusCode;
			response = response.text();

			if (process.env.APP_DEBUG) {
				console.info(`[${new Date().toISOString()}] - ${socket.id} authenticated for: ${options.form.channel_name}`);
			}

			try {
				body = JSON.parse(response);
			} catch (e) {
				body = response;
			}

			return body;
		} catch (error) {
			if (error) {
				if (process.env.APP_DEBUG) {
					console.error(`[${new Date().toISOString()}] - Error authenticating ${socket.id} for ${options.form.channel_name}`);
					console.error(error);
				}
				throw ({ reason: "Error sending authentication request.", status: 0 });
			} else if (status !== 200) {
				if (process.env.APP_DEBUG) {
					console.warn(`[${new Date().toISOString()}] - ${socket.id} could not be authenticated to ${options.form.channel_name}`);
					console.error(response);
				}

				throw ({ reason: "Client can not be authenticated, got HTTP status " + status, status: status });
			}
		}
	}

	prepareHeaders(socket, options) {
		options.headers["Cookie"] = options.headers["Cookie"] || socket.request.headers.cookie;
		options.headers["X-Requested-With"] = "XMLHttpRequest";
		// options.headers['Content-Type'] = 'application/x-www-form-urlencoded';

		return options.headers;
	}
};
