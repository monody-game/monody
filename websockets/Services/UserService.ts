import { Member, MemberList } from "./GameService.js";

export class UserService {
	static getUserBySocket(socket: string, users: MemberList): Member {
		return users.find((user) => user.socketId === socket) as Member;
	}
}
