const io = require("socket.io")(5000);
const jwt = require("jsonwebtoken");
const env = require("dotenv");

env.config();

let users = [];

io.on("connection", (socket) => {
    console.log("init socket");
    let currentUser = null;

    socket.on("chat.send", ({ author, content }) => {
      console.log('hey salut');  
      const messages = [];
        const message = {
            author: author,
            content: content,
        };
        messages.push(message);
        console.log(message);
        socket.broadcast.emit("chat.new", message);
        socket.emit("messages", { messages });
    });

    socket.on("game.connect", ({ token }) => {
        try {
            const decoded = jwt.verify(token, process.env.JWT_SECRET, {
                algorithms: ["HS256"],
            });
            currentUser = {
                id: decoded.user_id,
                username: decoded.user_name,
                count: 1,
            };
            const user = users.find((u) => u.id === currentUser.id);
            if (user) {
                user.count++;
            } else {
                users.push(currentUser);
                socket.broadcast.emit("users.new", { user: currentUser });
            }
            socket.emit("game.users", { users });
        } catch (e) {
            console.error(e.message);
        }
    });

    socket.on("game.disconnect", () => {
        console.log("game.disconnect");
        if (currentUser) {
            const user = users.find((u) => u.id === currentUser.id);
            if (user) {
                user.count--;
                if (user.count === 0) {
                    users = users.filter((u) => u.id !== currentUser.id);
                    socket.broadcast.emit("users.leave", { user: currentUser });
                }
            }
        }
    });
});
