const BaseResponder = require("./BaseResponder");
const {client} = require("../Redis/Connection");

module.exports = class ChatResponder extends BaseResponder {
  constructor() {
    super();
    this.respondTo = [
      /chat(\.\w*)+/
    ]
  }

  async emit(socket, data) {
    switch (data.event) {
      case "client-chat.werewolf.send":
        const game = JSON.parse(await client.get('game:' + data.channel.split('.')[1]))
        const members = await this.getMembers(data.channel)
        data.data.author = members.find(member => member.user_id === data.data.author).user_info

        members.forEach(member => {
          if (game.werewolves.indexOf(parseInt(member.user_id)) >= 0) {
            socket.to(member.socketId).emit("chat.werewolf", data.channel, data.data);
          }
        })
        break;
      default:
        console.warn("Unknown event: " + data.event);
        break;
    }
  }

  async getMembers(channel) {
    const members = JSON.parse(await client.get(channel + ':members'));
    if (!members) return [];
    return members;
  }
}
