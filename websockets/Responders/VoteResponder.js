const BaseResponder = require("./BaseResponder");

module.exports = class CounterResponder extends BaseResponder {
  constructor() {
    super();
    this.respondTo = [
      /^game.voting$/,
      /^game.unvoting$/
    ]
  }

  emit(socket, data) {
    console.log(data)
  }
};
