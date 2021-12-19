import { io } from "socket.io-client";

class GameLifeCycle {

  eventsList = [];

  constructor (eventList) {
    this.eventsList = eventList;
  }

  /**
   * Execute a callback when an event started
   * @param {String} event
   * @param {function} callback
   */
  async on (event, callback) {
    const socket = this.getSocket();
    if (this.eventsList.find((entry) => entry === event)) {
      return new Promise((resolve) => {
        socket.on(event, () => {
          resolve(callback);
        });
      });
    } else {
      throw new Error("Event name isn't valid");
    }
  }

  /**
   * Emit an event
   * @param {String} event
   * @param {*} params
   */
  emit (event, params = null) {
    const socket = this.getSocket();
    socket.emit(event, params);
  }

  getSocket () {
    return io('localhost:5000', { transports: ["websocket"] });
  }
}

export default GameLifeCycle;
