class CounterHandler {
    register(socket) {
        socket.on("counter.day", () => {
            socket.broadcast.emit("game.day");
        });

        socket.on("counter.night", () => {
            socket.broadcast.emit("game.night");
        });

        socket.on("counter.start", () => {
            console.log("Counter started !!");
        });

        socket.on("counter.update", () => {
            console.log("Counter update !!");
        });

        socket.on("counter.end", () => {
            console.log("Counter ended !!");
        });
    }
}

module.exports = CounterHandler
