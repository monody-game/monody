import Echo from "laravel-echo";

import io from "socket.io-client";

window.io = io;

window.Echo = new Echo({
	broadcaster: "socket.io",
	host: window.location.hostname + ":6001",
	transports: ["websocket", "polling", "flashsocket"],
	secure: true,
	forceTLS: true
});

setTimeout(() => {
	console.info("%cAttention !", "color: #273097; font-size: 64px; font-weight: bold");
	console.info("%cSi quelqu'un vous a demandé de coller quelque chose ici, il y a à peu près 100 chances sur 10 que ça soit une arnaque.", "font-size: 20px");
	console.info("%cFermez cette fenêtre sauf si vous savez exactement ce que vous faites.", "color: red; font-size: 20px");
}, 200);

window.addEventListener("devtoolschange", event => {
	if (event.detail.isOpen) {
		console.info("%cAttention !", "color: #273097; font-size: 64px; font-weight: bold");
		console.info("%cSi quelqu'un vous a demandé de coller quelque chose ici, il y a à peu près 100 chances sur 10 que ça soit une arnaque.", "font-size: 20px");
		console.info("%cFermez cette fenêtre sauf si vous savez exactement ce que vous faites.", "color: red; font-size: 20px");
	}
});
