import fetch from "../Helpers/fetch.js";

export default async function getRounds(gameId = 0) {
	const apiRounds = await fetch(`https://web/api/rounds/${gameId}`, {
		"method": "GET"
	});

	return apiRounds.json;
}
