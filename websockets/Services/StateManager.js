const {client} = require('../Redis/Connection');
const states = require('../Constants/GameStates')
const durations = require("../Constants/RoundDurations");
const loopingStates = require('../Constants/LoopingStates');
const {dump} = require("laravel-mix");

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
   * @param { object } channel
   * @param { number } counterId
   * @returns {Promise<{duration: number, state: number}>} context The next round context
   */
  async nextState(channel, counterId) {
    const currentState = (await this.getState(channel.split('.')[1]))['status'];
    let duration = 0;

    if (currentState === loopingStates[loopingStates.length - 1]) {
      duration = Object.values(durations)[loopingStates[0]];

      await this.setState({
        status: loopingStates[0],
        startTimestamp: Date.now(),
        counterDuration: duration,
        counterId: counterId
      }, channel)

      return {
        duration,
        state: loopingStates[0]
      };
    }

    const nextState = currentState + 1;

    if (!Object.values(states).includes(nextState)) {
      throw new Error('Game is supposed to be ended');
    }

    duration = Object.values(durations)[nextState];

    await this.setState({
      status: Object.values(states)[nextState],
      startTimestamp: Date.now(),
      counterDuration: duration,
      counterId: counterId
    }, channel)

    return {
      duration,
      state: nextState
    };
  }

  async getNextStateDuration(channel) {
    const currentState = (await this.getState(channel.split('.')[1]))['status'];

    if (currentState === loopingStates[loopingStates.length - 1]) {
      return Object.values(durations)[loopingStates[0]];
    }

    return Object.values(durations)[currentState + 1];
  }
}
