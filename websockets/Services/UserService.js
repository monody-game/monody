export class UserService {
	static getUserBySocket(socket, users) {
		return users.find(user => user.socketId === socket);
	}
}
