const {client} = require('../Redis/Connection');
const states = require('../Constants/GameStates')

module.exports = class StateManager {
  constructor(io) {
    this.io = io
  }

  /**
   * Set the current state of a game
   *
   * @param { Object } state
   * @param { Object } channel
   * @returns self
   */
  async setState(state, channel) {
    if (!Object.values(states).includes(state.status)) {
      throw new Error('Invalid state')
    }

    await client.set(`game:${channel.split('.')[1]}:state`, JSON.stringify(state));

    this.io.to(channel).emit('game.', channel);

    return this;
  }

  /**
   * Get the current state of a game
   *
   * @param { String } gameId
   * @returns { String }
   */
  async getState(gameId) {
    return await client.get(`game:${gameId}:state`);
  }
}
