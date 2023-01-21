import Echo from "laravel-echo";
import io from "socket.io-client";

window.io = io;

window.Echo = new Echo({
	broadcaster: "socket.io",
	host: "ws.monody.fr/",
	transports: ["websocket"],
	secure: true,
	forceTLS: true
});
