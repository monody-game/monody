const io = require("socket.io")(5000, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"],
        credentials: true
    },
});
const env = require("dotenv");
const ConnectionHandler = new (require('./node/Game/ConnectionHandler'))
const VoteHandler = new (require('./node/Game/VoteHandler'))
const CounterHandler = new (require('./node/Game/CounterHandler'))

env.config();

io.on("connection", (socket) => {
    socket.on("chat.send", ({ author, content }) => {
        const messages = [];
        const message = {
            author,
            content,
        };
        messages.push(message);
        socket.broadcast.emit("chat.new", message);
        socket.emit("messages", { messages });
    });

    CounterHandler.register(socket)

    VoteHandler.register(socket)

    ConnectionHandler.register(socket)
});
