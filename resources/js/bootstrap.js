import Echo from "laravel-echo";

window.io = require("socket.io-client");

window.Echo = new Echo({
	broadcaster: "socket.io",
	host: window.location.hostname + ":6001",
	transports: ["websocket", "polling", "flashsocket"],
	secure: true,
	forceTLS: true
});

setTimeout(() => {
	console.log("%cAttention !", "color: #273097; font-size: 64px; font-weight: bold");
	console.log("%cSi quelqu'un vous a demandé de coller quelque chose ici, il y a à peu près 100 chances sur 10 que ça soit une arnaque.", "font-size: 20px");
	console.log("%cFermez cette fenêtre sauf si vous savez exactement ce que vous faites.", "color: red; font-size: 20px");
}, 200);

window.addEventListener("devtoolschange", event => {
	if (event.detail.isOpen) {
		console.log("%cAttention !", "color: #273097; font-size: 64px; font-weight: bold");
		console.log("%cSi quelqu'un vous a demandé de coller quelque chose ici, il y a à peu près 100 chances sur 10 que ça soit une arnaque.", "font-size: 20px");
		console.log("%cFermez cette fenêtre sauf si vous savez exactement ce que vous faites.", "color: red; font-size: 20px");
	}
});
