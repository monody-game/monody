export async function send(message, selectedChat) {
	if (message === "") return;

	const gameId = window.location.pathname.split("/")[2];

	await window.JSONFetch("/game/message/send", "POST", {
		content: message,
		gameId,
		couple: selectedChat === "couple",
	});
}
