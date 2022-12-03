export default async function exists({ router, to }) {
	if (!to.params.id) {
		router.push({ name: "play" });
		return;
	}

	const response = await window.JSONFetch("/game/check", "POST", {
		gameId: to.params.id,
	});

	if (response.status !== 200) {
		router.push({ name: "play" });
	}
}
