import { useStore } from "../../stores/alerts.js";


export default async function canJoin({ router, to }) {
	const alerts = useStore();
	const res = await fetch("/broadcasting/auth", {
		method: "POST",
		headers: {
			"Content-type": "application/json; charset=UTF-8"
		},
		credentials: "include",
		body: JSON.stringify({
			socket_id: window.Echo.socketId(),
			channel_name: `presence-game.${to.params.id}`
		})
	});

	if (res.status === 403) {
		alerts.addAlerts({
			"error":	"Vous ne pouvez rejoindre cette partie."
		});
		router.push("/play");
	}
}
