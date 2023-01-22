import { useStore as useAlertStore } from "./stores/alerts.js";
import { useStore as usePopupStore } from "./stores/popup.js";
import { useStore as useDebugStore } from "./stores/debug-bar.js";

/**
 * @param {String} url
 * @param {String} method
 * @param {Object} body
 */
window.JSONFetch = async (url, method = "GET", body = null) => {
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

	const response = await fetch("/api" + url, params);

	if (response.status === 204) {
		res.data = {};
		res.status = response.status;

		return res;
	}

	const content = await response.json();

	if (!response.ok) {
		useDebugStore().errors.push({
			status: response.statusText,
			target: response.url,
			content: {
				message: content.message,
				exception: content.exception,
				file: content.file,
				line: content.line
			}
		});
	}

	res.data = content;

	if (!res.data) {
		res.data = {};
	}

	if (res.data.alerts) {
		useAlertStore().addAlerts(res.data.alerts);
	}

	if (res.data.popups) {
		usePopupStore().setPopup(res.data.popups);
	}

	res.status = response.status;

	return res;
};
