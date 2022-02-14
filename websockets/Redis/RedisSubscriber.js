const { client } = require('./Connection')

module.exports.RedisSubscriber = class {
  constructor() {
    this.sub = client.duplicate();

    this.sub.connect();
  }

  async subscribe(callback) {
    await this.sub.pSubscribe('*', (message, channel) => {
      if (process.env.APP_DEBUG) {
        console.info('Channel: ' + channel)
        console.info('Event: ' + JSON.parse(message).event)
      }
      return callback(channel, JSON.parse(message))
    });
  }

  async unsubscribe() {
    await this.sub.pUnsubscribe('*');
    await this.sub.disconnect();
  }
}
