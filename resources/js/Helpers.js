import { useStore as useAlertStore } from "./stores/alerts.js";
import { useStore as usePopupStore } from "./stores/modals/popup.js";
import { useStore as useDebugStore } from "./stores/debug-bar.js";
import { useCache } from "./composables/cache.js";

const lang = localStorage.getItem("lang");

/**
 * @param {String} url
 * @param {String} method
 * @param {Object} body
 */
window.JSONFetch = async (url, method = "GET", body = null) => {
	const cache = useCache();

	if (
		cache.exists(url) &&
		Date.parse(cache.get(url).until) > Date.now() &&
		method === "GET"
	) {
		return cache.get(url).response;
	}

	const headers = {
		"Content-type": "application/json; charset=UTF-8",
		Accept: "application/json",
	};

	if (lang !== null) {
		headers["X-App-Locale"] = lang;
	}

	const params = {
		method: method,
		headers,
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
		res.ok = true;

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
				line: content.line,
			},
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
	res.ok = response.ok;

	if ("data" in res.data) {
		res.data = res.data.data;
	}

	if (res.ok && content.meta.cache.cache === true) {
		cache.set(url, {
			until: content.meta.cache.until,
			response: res,
		});

		if (content.meta.cache.flush === true) {
			cache.clear();
			return res;
		}

		for (const route of content.meta.cache.flush) {
			cache.flush(route);
		}
	}

	return res;
};
