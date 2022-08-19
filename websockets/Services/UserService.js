export class UserService {
	static async getUserBySocket(socket, users) {
		return users.find(user => user.socketId === socket);
	}
}
