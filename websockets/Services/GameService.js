const {client} = require("../Redis/Connection");
const RoleManager = require("./RoleManager");
const StateManager = require("./StateManager");
const UserService = require("./UserService");
const states = require('../Constants/GameStates');
const durations = require('../Constants/RoundDurations');
const werewolves = [1];

module.exports = class GameService {
  constructor(io) {
    this.io = io
    this.StateManager = new StateManager(io);
  }

  async getGame(id) {
    return JSON.parse(await client.get('game:' + id));
  }

  async setGame(id, data) {
    await client.set('game:' + id, JSON.stringify(data));
  }

  async isAuthor(socket, gameId) {
    const game = await this.getGame(gameId);
    const members = JSON.parse(await client.get('presence-game:' + gameId + ':members')) ?? [];
    const userId = await UserService.getUserBySocket(socket, members);
    return game.author === userId;
  }

  async startGame(channel, game, members) {
    game.is_started = false;
    await this.setGame(channel.split('.')[1], game);

    this.StateManager.setState({
      status: states.GAME_STARTING,
      startTimestamp: Date.now(),
      counterDuration: durations.STARTING_DURATION
    }, channel);

    if (process.env.APP_DEBUG) {
      console.info(`[${new Date().toISOString()}] - Starting game id ${channel.split('.')[1]}\n`);
    }

    setTimeout(() => {
      this.roleManagement(game, channel, members)
    }, 6000)
  }

  async roleManagement(game, channel, members) {
    const gameWerewolves = [];
    game.assigned_roles = RoleManager.assign(game.roles, members);

    Object.keys(game.assigned_roles).forEach(member => {
      if (werewolves.indexOf(game.assigned_roles[member]) >= 0) gameWerewolves.push(parseInt(member))
    })
    game.werewolves = gameWerewolves;

    for (const member of members) {
      const user = await UserService.getUserBySocket(member.socketId, members);
      this.io.to(member.socketId).emit('game.role-assign', channel, game.assigned_roles[user.user_id])
    }

    await client.set('game:' + channel.split('.')[1], JSON.stringify(game));
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
