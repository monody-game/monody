const {client} = require('../Redis/Connection')
const states = require("../Constants/GameStates");
const durations = require("../Constants/RoundDurations");

module.exports.PresenceChannel = class {
  constructor(io) {
    this.io = io;
    this.gameService = new (require('../Services/GameService'))(io);
    this.StateManager = new (require('../Services/StateManager'))(io);
  }

  async getMembers(channel) {
    const members = JSON.parse(await client.get(`game:${channel.split('.')[1]}:members`));
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
    await client.set(`game:${channel.split('.')[1]}:members`, JSON.stringify(members));
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

    await client.set(`game:${channel.split('.')[1]}:members`, JSON.stringify(members));

    this.onSubscribed(socket, channel, members);

    if (!isMember) {
      this.onJoin(socket, channel, member);
    }

    const id = channel.split('.')[1]
    const count = await this.gameService.getRolesCount(id)
    const game = JSON.parse(await client.get('game:' + id))

    if (await this.gameService.isAuthor(socket, id)) {
      this.StateManager.setState({
        status: states.GAME_WAITING,
        startTimestamp: Date.now(),
        counterDuration: durations.WAITING_DURATION
      }, channel);
    }

    if (members.length === count && game.is_started === false) {
      await this.gameService.startGame(channel, game, members)
    }
  }

  async leave(socket, channel) {
    const gameId = channel.split('.')[1]
    let members = await this.getMembers(channel)
    members = members || []

    let member = members.find(m => m.socketId === socket.id)
    if (!member) return;
    members = members.filter(m => m.socketId !== member.socketId)

    if (members.length === 0) {
      await client.del(channel + ':members')
      await client.del('game:' + gameId)
      await client.del(`game:${gameId}:state`)

      this.onDelete(gameId)
      this.onLeave(channel, member)
    } else {
      await client.set(`game:${channel.split('.')[1]}:members`, JSON.stringify(members));
      const game = JSON.parse(await client.get('game:' + gameId))
      game.users = members
      await client.set('game:' + gameId, JSON.stringify(game))

      const isMember = await this.isMember(channel, member);

      if (!isMember && member) {
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
}
