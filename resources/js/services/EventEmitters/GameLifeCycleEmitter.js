import EventEmitter from "./EventEmitter";

class GameLifeCycle extends EventEmitter {
  constructor () {
    super([
      "game.created",
      "game.start",
      "game.vote",
      "game.day",
      "game.night",
      "game.werewolf.vote",
      "game.end",
      "game.destroy",
      "counter.night",
      "counter.day"
    ]);
  }
}

export default GameLifeCycle;
