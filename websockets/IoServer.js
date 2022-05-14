const {Server} = require('socket.io');
const {RedisSubscriber} = require('./Redis/RedisSubscriber');
const {ResponderManager} = require("./Responders/ResponderManager");
const {Channel} = require("./Channels/Channel")

module.exports.IoServer = class {
  responders = [];

  constructor() {
    this.server = new Server({}, {
      cors: {
        credentials: true
      }
    });
    this.subscriber = new RedisSubscriber();
    this.channel = new Channel(this.server)
  }

  async start() {
    console.info("\nStarting IoServer...");

    if (process.env.APP_DEBUG) {
      console.info("\nIoServer is running in debug mode.\n");
    }

    this.initResponders();
    this.onConnect();
    await this.listen();
    this.server.listen(6001);
  }

  async listen() {
    await this.subscriber.subscribe((channel, message) => {
      this.broadcast(channel, message);
    });
  }

  find(id) {
    return this.server.sockets.sockets[id];
  }

  broadcast(channel, message) {
    if (message.socket && this.find(message.socket)) {
      this.find(message.socket).broadcast.to(channel).emit(message.event, channel, message.data);
    } else {
      this.server.to(channel).emit(message.event, channel, message);
    }
  }

  onConnect() {
    this.server.on('connection', (socket) => {
      this.onSubscribe(socket);
      this.onUnsubscribe(socket);
      this.onDisconnecting(socket);
      this.onClientEvent(socket);
    });
  }

  onSubscribe(socket) {
    socket.on('subscribe', async (data) => {
      if (data.channel) {
        const responder = ResponderManager.findResponder('subscribe', this.responders);
        await responder.emit(socket, data)
      }
      await this.channel.join(socket, data)
    });
  }

  onUnsubscribe(socket) {
    socket.on('unsubscribe', async (data) => {
      await this.channel.leave(socket, data.channel, 'unsubscribed')
    });
  }

  onDisconnecting(socket) {
    socket.on('disconnecting', (reason) => {
      Object.keys(socket.rooms).forEach(async (room) => {
        if (room !== socket.id) {
          await this.channel.leave(socket, room, reason)
        }
      });
    });
  }

  onClientEvent(socket) {
    socket.on('client event', async (data) => {
      if (data.event && data.channel) {
        const responder = ResponderManager.findResponder(data.event, this.responders);
        await responder.emit(socket, data)
      }
    });
  }

  initResponders() {
    ResponderManager.getAll().forEach(responder => {
      this.responders.push(new responder(this.server));
    });
  }
}
