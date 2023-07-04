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

function flush(key, storage) {
	if (exists(key, storage)) {
		delete storage[key];
	}
}

export function useCache() {
	let storage = localStorage.getItem("cache") ?? "{}";
	storage = JSON.parse(storage);

	return {
		get: (key) => get(key, storage),
		set: (key, value) => {
			localStorage.setItem("cache", JSON.stringify(set(key, value, storage)));
		},
		exists: (key) => exists(key, storage),
		flush: (key) => flush(key, storage),
		clear: () => localStorage.removeItem("cache"),
	};
}
