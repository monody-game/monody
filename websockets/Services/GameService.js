const {client} = require("../Redis/Connection");
const RoleManager = require("./RoleManager");
const StateManager = require("./StateManager");
const CounterService = require("./CounterService");
const UserService = require("./UserService");
const states = require('../Constants/GameStates');
const durations = require('../Constants/RoundDurations');
const werewolves = [1];

module.exports = class GameService {
  constructor(io) {
    this.io = io
    this.StateManager = new StateManager(io);
    this.counterService = new CounterService(io);
  }

  async getGame(id) {
    return JSON.parse(await client.get('game:' + id));
  }

  async setGame(id, data) {
    await client.set('game:' + id, JSON.stringify(data));
  }

  async isAuthor(socket, gameId) {
    const game = await this.getGame(gameId);
    if (!game) return false;

    const members = JSON.parse(await client.get('game:' + gameId + ':members')) ?? [];
    const user = await UserService.getUserBySocket(socket, members);

    if (!user) return false;

    return game.owner === user.user_id;
  }

  async startGame(channel, game, members) {
    // TODO : CHANGE !!!!
    game.is_started = false;
    const gameId = channel.split('.')[1];
    await this.setGame(gameId, game);

    this.StateManager.setState({
      status: states.GAME_STARTING,
      startTimestamp: Date.now(),
      counterDuration: durations.STARTING_DURATION
    }, channel);

    if (process.env.APP_DEBUG) {
      console.info(`[${new Date().toISOString()}] - Starting game id ${channel.split('.')[1]}\n`);
    }

    this.startTimeout = await new Promise((resolve, reject) => {
      const id = setTimeout(async () => {
        await this.roleManagement(game, channel, members)
        resolve(id)
      }, 6000)
    })

    await this.counterService.cycle(channel)
  }

  async stopGameLaunch(channel) {
    if (this.startTimeout) {
      clearTimeout(this.startTimeout)
    }
    await this.StateManager.setState({
      status: states.GAME_WAITING,
      startTimestamp: Date.now(),
      counterDuration: -1
    }, channel)
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
