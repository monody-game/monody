const StateManager = require('./StateManager')

module.exports = class CounterService {
  constructor(io) {
    this.io = io
  }

  async cycle(channel) {
    const manager = new StateManager(this.io)

    const id = setTimeout(async () => {
      await this.cycle(channel)
    }, (await manager.getNextStateDuration(channel) + 1) * 1000)

    await manager.nextState(channel, id[Symbol.toPrimitive]())
  }
}
