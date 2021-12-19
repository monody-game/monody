import EventEmitter from "./EventEmitter";

/**
 * @function
 */
class GameLifeCycle extends EventEmitter {
  constructor () {
    super([
      "counter.start",
      "counter.update",
      "counter.end"
    ]);
  }
}

export default GameLifeCycle;
