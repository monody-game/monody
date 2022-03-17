module.exports = class UserService {
  static async getUserBySocket(socket, users) {
    return users.find(user => user.socket === socket);
  }
}
