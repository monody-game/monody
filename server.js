const io = require("socket.io")(5000, {
    cors: {
        origin: "*",
    },
});
const jwt = require("jsonwebtoken");
const env = require("dotenv");

env.config();

let users = [];

io.on("connection", (socket) => {
    let currentUser = null;

    socket.on("counter.day", () => {
        console.log("day");
        socket.broadcast.emit("game.day");
    });

    socket.on("counter.night", () => {
        console.log("night");
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

    socket.on("chat.send", ({ author, content }) => {
        const messages = [];
        const message = {
            author: author,
            content: content,
        };
        messages.push(message);
        socket.broadcast.emit("chat.new", message);
        socket.emit("messages", { messages });
    });

    socket.on("game.voting", ({ voted_user, voted_by }) => {
        socket.broadcast.emit("game.vote", { voted_user, voted_by });
    });

    socket.on("game.unvoting", ({ voted_user, voted_by }) => {
        socket.broadcast.emit("game.unvote", { voted_user, voted_by });
    });

    socket.on("game.connect", ({ token }) => {
        try {
            const decoded = jwt.verify(token, process.env.JWT_SECRET, {
                algorithms: ["HS256"],
            });
            currentUser = {
                id: decoded.user_id,
                username: decoded.user_name,
                avatar: decoded.user_avatar,
                count: 1,
            };
            const user = users.find((u) => u.id === currentUser.id);
            if (user) {
                /* 
                user.count++;
            } else { */
                users.push(currentUser);
                socket.broadcast.emit("game.users.new", { user: currentUser });
            }
            socket.emit("game.users", { users });
        } catch (e) {
            console.error(e.message);
        }
    });

    socket.on("game.disconnect", () => {
        if (currentUser) {
            const user = users.find((u) => u.id === currentUser.id);
            if (user) {
                /* 
                user.count--;
                if (user.count === 0) { */
                users = users.filter((u) => u.id !== currentUser.id);
                socket.broadcast.emit("game.users.leave", {
                    user: currentUser,
                }); /* 
                } */
            }
        }
    });
});
