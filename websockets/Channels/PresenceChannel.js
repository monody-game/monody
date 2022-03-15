const {client} = require('../Redis/Connection')

module.exports.PresenceChannel = class {

  constructor(io) {
    this.io = io
  }

  async getMembers(channel) {
    const members = JSON.parse(await client.get(channel + ':members'));
    if (!members) return [];
    return members
  }

  async isMember(channel, member) {
    let members = await this.getMembers(channel);
    members = await this.removeInactive(channel, members)
    const search = members.filter(m => m.user_id === member.user_id)
    return search && search.length;
  }

  async removeInactive(channel, members) {
    const clients = await this.io.of("/").in(channel).allSockets()
    members = members.filter(member => {
      return Array.from(clients).indexOf(member.socketId) >= 0;
    })
    await client.set(channel + ':members', JSON.stringify(members));
    return members
  }

  async join(socket, channel, member) {
    if (!member) {
      return;
    }

    const isMember = await this.isMember(channel, member)
    let members = await this.getMembers(channel)

    member.socketId = socket.id
    members.push(member)

    await client.set(channel + ':members', JSON.stringify(members))

    this.onSubscribed(socket, channel, members);

    if (!isMember) {
      this.onJoin(socket, channel, member);
    }

    const count = await this.getRolesCount(channel.split('.')[1])
    const game = JSON.parse(await client.get('game:' + channel.split('.')[1]))

    if(members.length === count && game.is_started === false) {
      await this.startGame(channel, game)
    }
  }

  async leave(socket, channel) {
    let members = await this.getMembers(channel)
    members = members || []

    let member = members.find(m => m.socketId === socket.id)
    members = members.filter(m => m.socketId !== member.socketId)

    if(members.length === 0) {
      await client.del(channel + ':members')
      await client.del('game:' + channel.split('.')[1])

      this.onDelete(channel.split('.')[1])
      this.onLeave(channel, member)
    } else {
      await client.set(channel + ':members', JSON.stringify(members))
      const game = JSON.parse(await client.get('game:' + channel.split('.')[1]))
      game.users = members
      await client.set('game:' + channel.split('.')[1], JSON.stringify(game))

      const isMember = await this.isMember(channel, member);

      if(!isMember && member) {
        delete member.socketId;
        this.onLeave(channel, member)
      }
    }
  }

  onJoin(socket, channel, member) {
    this.io.sockets.sockets.get(socket.id).broadcast.to(channel).emit('presence:joining', channel, member);
  }

  onLeave(channel, member) {
    this.io.to(channel).emit('presence:leaving', channel, member);
  }

  onDelete(gameId) {
    this.io.to('home').emit('game.delete', 'home', gameId)
  }

  onSubscribed(socket, channel, members) {
    this.io.to(socket.id).emit('presence:subscribed', channel, members);
  }

  async getRolesCount(gameId) {
    const game = JSON.parse(await client.get('game:' + gameId))
    let count = 0;

    for (const role in game.roles) {
      count += game.roles[role]
    }
    return count;
  }

  async startGame(channel, game) {
    const id = channel.split('.')[1];
    game.is_started = true;
    await client.set('game:' + id, JSON.stringify(game))

    this.io.to(channel).emit('game.start', channel);

    if (process.env.APP_DEBUG) {
      console.info(`[${new Date().toISOString()}] - Starting game id ${id}\n`);
    }
  }
}
