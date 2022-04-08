const BaseResponder = require("./BaseResponder");

module.exports = class CounterResponder extends BaseResponder {
  constructor() {
    super();
    this.respondTo = [
      /^counter\.end$/,
      /^counter\.ping$/
    ]
  }

  emit(socket, data) {
    switch (data.event) {
      case "client-counter.ping":
        socket.broadcast.emit('counter.update', data.data);
        break;
      case "client-counter.end":
        socket.emit('game.newDay', data.channel)
        break;
      default:
        console.warn("Unknown event: " + data.event);
        break;
    }
  }
};
