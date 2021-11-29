const jwt = require("jsonwebtoken");

class ConnectionHandler {
    users = []

    connect(token) {
        try {
            const decoded = jwt.verify(token, process.env.JWT_SECRET, {
                algorithms: ["HS256"],
            });
            this.currentUser = {
                id: decoded.user_id,
                username: decoded.user_name,
                avatar: decoded.user_avatar,
                count: 1,
            };
            const user = this.users.find((u) => u.id === this.currentUser.id);
            if (user) {
                user.count++;
            } else {
                this.users.push(this.currentUser);
                this.socket.broadcast.emit("game.users.new", { user: this.currentUser });
            }
            this.socket.emit("game.users", { users: this.users });
        } catch (e) {
            console.error(e.message);
        }
    }

    disconnect() {
        if (this.currentUser) {
            const user = this.users.find((u) => u.id === this.currentUser.id);
            if (user) {
                user.count--;
                if (user.count === 0) {
                    this.users = this.users.filter((u) => u.id !== this.currentUser.id);
                    this.socket.broadcast.emit("game.users.leave", {
                        user: this.currentUser,
                    });
                }
            }
        }
    }

    register(socket) {
        this.socket = socket

        socket.on("game.connect", ({ token }) => {
            this.connect(token)
        });

        socket.on("game.disconnect", () => {
            this.disconnect()
        });
    }
}

module.exports = ConnectionHandler
