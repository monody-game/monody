export default class Body {
	static make (content) {
		const body = new URLSearchParams();

		for (const bodyKey in content) {
			if (["object", "array"].includes(typeof content[bodyKey])) {
				body.set(bodyKey, JSON.stringify(content[bodyKey]));
				continue;
			}

			body.set(bodyKey, content[bodyKey]);
		}

		return body;
	}
}
