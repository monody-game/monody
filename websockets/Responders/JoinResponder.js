const BaseResponder = require("./BaseResponder");
const StateManager = new (require("../Services/StateManager"))();

module.exports = class JoinResponder extends BaseResponder {
  constructor() {
    super();
    this.respondTo = [
      /^subscribe$/
    ]
  }

  emit(socket, data) {
    const id = data.channel.split('.')[1];
    const game = StateManager.getState(id);

    console.log("JoinResponder");
    console.log(data)
  }
};
