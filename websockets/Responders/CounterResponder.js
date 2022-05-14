const BaseResponder = require("./BaseResponder");
const StateManager = require('../Services/StateManager');

module.exports = class CounterResponder extends BaseResponder {
  constructor() {
    super();
    this.respondTo = [
      /^counter\.end$/,
      /^counter\.start$/,
    ]
  }

  async emit(socket, data) {
    const manager = new StateManager(socket);
    switch (data.event) {
      case "client-counter.start":
        socket.emit("counter.time", data.channel, {
          duration: data.data.duration,
          start: data.data.starting_timestamp
        });
        break;
      case "client-counter.end":
        const state = await manager.getState(data.channel.split('.')[1])
        const currentSeconds = new Date().getSeconds()
        const stateSeconds = new Date((state.startTimestamp + state.counterDuration * 1000)).getSeconds()
        if (currentSeconds === stateSeconds) {
          manager.nextState(data.channel);
        }
        break;
      default:
        console.warn("Unknown event: " + data.event);
        break;
    }
  }
};
