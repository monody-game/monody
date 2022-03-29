const BaseResponder = require("./BaseResponder");

module.exports = class CounterResponder extends BaseResponder {
  constructor() {
    super();
    this.respondTo = [
      /^counter\.end$/
    ]
  }

  emit(socket, data) {
    socket.emit('game.newDay', data.channel)
  }
};
