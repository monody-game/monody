function exists(key, storage) {
	return key in storage;
}

function get(key, storage) {
	if (exists(key, storage)) {
		return storage[key];
	}

	return null;
}

function set(key, value, storage) {
	storage[key] = value;

	return storage;
}

function flush(keys, storage) {
	for (const key of keys) {
		if (exists(key, storage)) {
			delete storage[key];
		}
	}
	return storage;
}

export function useCache() {
	let storage = sessionStorage.getItem("cache") ?? "{}";
	storage = JSON.parse(storage);

	return {
		get: (key) => get(key, storage),
		set: (key, value) => {
			sessionStorage.setItem("cache", JSON.stringify(set(key, value, storage)));
		},
		exists: (key) => exists(key, storage),
		flush: (...keys) => {
			sessionStorage.setItem("cache", JSON.stringify(flush(keys, storage)));
		},
		clear: () => sessionStorage.removeItem("cache"),
	};
}
