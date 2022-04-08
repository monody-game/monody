const {client} = require("../Redis/Connection");
const RoleManager = require("./RoleManager");
const UserService = require("./UserService");
const werewolves = [1];

module.exports = class GameService {
  constructor(io) {
    this.io = io
  }

  async getGame(id) {
    return JSON.parse(await client.get('game:' + id));
  }

  async setGame(id, data) {
    await client.set('game:' + id, JSON.stringify(data));
  }

  async startGame(channel, game, members) {
    game.is_started = true;
    await this.setGame(channel.split('.')[1], game);

    this.io.to(channel).emit('game.start', channel);

    if (process.env.APP_DEBUG) {
      console.info(`[${new Date().toISOString()}] - Starting game id ${id}\n`);
    }

    setTimeout(() => {
      this.roleManagement(game, members)
    }, 6000)
  }

  async roleManagement(game, members) {
    const gameWerewolves = [];
    game.assigned_roles = RoleManager.assign(game.roles, members);

    Object.keys(game.assigned_roles).forEach(member => {
      if (werewolves.indexOf(game.assigned_roles[member]) >= 0) gameWerewolves.push(parseInt(member))
    })
    game.werewolves = gameWerewolves;

    await members.forEach(async (member) => {
      const user = await UserService.getUserBySocket(member.socketId, members);
      this.io.to(member.socketId).emit('game.role-assign', channel, game.assigned_roles[user.user_id])
    })
    await client.set('game:' + game.id, JSON.stringify(game));
    this.io.to(channel).emit('game.assign', channel);
  }

  async getRolesCount(gameId) {
    const game = await this.getGame(gameId);
    if (!game) return;
    let count = 0;

    for (const role in game.roles) {
      count += game.roles[role]
    }
    return count;
  }
}
