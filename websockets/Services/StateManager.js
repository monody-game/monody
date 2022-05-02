const {client} = require('../Redis/Connection');
const states = require('../Constants/GameStates')
const durations = require("../Constants/RoundDurations");

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

    this.io.to(channel).emit('game.state', channel, {
      state: Object.keys(states)[state.status],
      counterDuration: state.counterDuration,
      startTimestamp: state.startTimestamp
    });

    return this;
  }

  /**
   * Get the current state of a game
   *
   * @param { String } gameId
   * @returns { Promise<Object> }
   */
  async getState(gameId) {
    return JSON.parse(await client.get(`game:${gameId}:state`));
  }

  /**
   * Switch the state to the next
   *
   * @param { Object } channel
   * @returns void
   */
  async nextState(channel) {
    const currentState = (await this.getState(channel.split('.')[1]))['status'];
    const nextState = currentState + 1;

    if (!Object.keys(states).includes(nextState)) {
      throw new Error('Game is supposed to be ended');
    }
    await this.setState({
      status: Object.keys(states)[nextState],
      startTimestamp: Date.now(),
      counterDuration: durations
    }, channel)
  }
}
