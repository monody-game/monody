const BaseResponder = require("./BaseResponder");

module.exports = class CounterResponder extends BaseResponder {
  constructor() {
    super();
    this.respondTo = [
      /^counter\.end$/,
      /^counter\.start$/,
    ]
  }

  emit(socket, data) {
    switch (data.event) {
      case "client-counter.start":
        socket.emit("counter.time", data.channel, {
          duration: data.data.duration,
          start: data.data.starting_timestamp
        });
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
