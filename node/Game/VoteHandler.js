class VoteHandler {
    register(socket) {
        socket.on("game.voting", ({ voted_user, voted_by }) => {
            socket.broadcast.emit("game.vote", { voted_user, voted_by });
        });

        socket.on("game.unvoting", ({ voted_user, voted_by }) => {
            socket.broadcast.emit("game.unvote", { voted_user, voted_by });
        });
    }
}

module.exports = VoteHandler
