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
module.exports = async function(url, opts, socket) {
	opts = {
		...opts,
		headers: {
			Cookie: socket.request.headers.cookie,
			"X-Requested-With": "XMLHttpRequest"
		}
	};

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
