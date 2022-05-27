/**
 * @param {String} url
 * @param {String} method
 * @param {Object} body
 */
window.JSONFetch = async (url, method, body = null) => {
	const params = {
		method: method,
		headers: {
			"Content-type": "application/json; charset=UTF-8",
		},
		credentials: "include",
	};
	const res = {};

	if (params.method.toLowerCase() !== "get") {
		params.body = JSON.stringify(body);
	}

	const response = await fetch("/api" + url, params).catch((err) =>
		res.error = err
	);

	if (response.status !== 204) {
		res.data = await response.json();
	}

	if (!res.data) {
		res.data = {};
	}

	res.status = response.status;

	return res;
};
