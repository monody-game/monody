export default async function exists({ router, to }) {
	if (!to.params.id) {
		router.push({ name: "play" });
		return;
	}

	const response = await window.JSONFetch("/game/check", "POST", {
		game_id: to.params.id,
	});

	if (response.status === "404") {
		router.push({ name: "play" });
	}
}
