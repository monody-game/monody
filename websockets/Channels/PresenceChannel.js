const {client} = require('../Redis/Connection')

module.exports.PresenceChannel = class {

  constructor(io) {
    this.io = io
  }

  async getMembers(channel) {
    return JSON.parse(await client.get(channel + ':members'))
  }

  async isMember(channel, member) {
    let members = await this.getMembers(channel);
    members = await this.removeInactive(channel, members, member)
    const search = members.filter(m => m.user_id === member.user_id)
    return search && search.length;
  }

  async removeInactive(channel, members, member) {
    const truc = this.io.of("/").in("channel")
    return [...members]
  }

  async join(socket, channel, member) {
    if (!member) {
      return;
    }

    const isMember = await this.isMember(channel, member)
    let members = this.getMembers(channel)

    members = members || []
    member.socketId = socket.id
    members.push(member)

    await client.set(channel + ':members', JSON.stringify(members))

    members = members.filter((item, index) => members.indexOf(item) !== index)

    this.onSubscribed(socket, channel, members);

    if (!isMember) {
      this.onJoin(socket, channel, member);
    }
  }

  async leave(socket, channel) {
    let members = await this.getMembers(channel)
    members = members || []

    let member = members.find(m => m.socketId === socket.id)
    members = members.filter(m => m.socketId !== member.socketId)

    await client.set(channel + ':members', JSON.stringify(members))

    const isMember = await this.isMember(channel, member);

    if(!isMember && member) {
      delete member.socketId;
      this.onLeave(channel, member)
    }
  }

  onJoin(socket, channel, member) {
    this.io.sockets.sockets[socket.id].broadcast.to(channel).emit('presence:joining', channel, member);
  }

  onLeave(channel, member) {
    this.io.to(channel).emit('presence:leaving', channel, member);
  }

  onSubscribed(socket, channel, members) {
    this.io.to(socket.id).emit('presence:subscribed', channel, members);
  }
}
