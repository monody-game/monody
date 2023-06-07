export class ChatService {
    static send(socket, channel, message, type = "message", to = null) {
        if (to) {
            socket = socket.to(to);
        }
        socket.emit("chat.send", channel, {
            data: {
                payload: {
                    content: message,
                    type
                }
            }
        });
    }
    static message = (socket, channel, message, to = null) => {
        this.send(socket, channel, message, undefined, to);
    };
    static info = (socket, channel, message, to = null) => {
        this.send(socket, channel, message, "info", to);
    };
    static error = (socket, channel, message, to = null) => {
        this.send(socket, channel, message, "error", to);
    };
}
