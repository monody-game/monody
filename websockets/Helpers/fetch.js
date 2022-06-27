const fetch = require("node-fetch");
const https = require("node:https");

const agent = new https.Agent({
	rejectUnauthorized: false,
});

/**
 * Use node-fetch
 *
 * @param { String } url
 * @param { Object } opts
 * @param socket
 * @returns {Promise<Object>}
 */
module.exports = async function(url, opts, socket = null) {
	opts = {
		...opts,
		headers: {
			"X-Requested-With": "XMLHttpRequest"
		}
	};

	if (socket) {
		opts.headers.Cookie = socket.request.headers.cookie;
	}

	const response = await fetch(url, {
		...opts,
		rejectUnauthorized: false,
		credentials: "include",
		agent
	});

	const body = await response.text();
	let json = {};

	try {
		json = JSON.parse(body);
	} catch (e) {
		//
	}

	return {
		json,
		text: body,
		status: response.status
	};
};
