import fetch from "../Helpers/fetch.js";

export class RoleService {
	static assign(roles, members) {
		const assigned = {};

		for (const role in roles) {
			if (parseInt(roles[role]) > 1) {
				for (let i = 0; i < parseInt(roles[role]); i++) {
					const member = this.pickUser(members, assigned);
					assigned[member] = parseInt(role);
				}
				continue;
			}
			assigned[this.pickUser(members, assigned)] = parseInt(role);
		}

		return assigned;
	}

	/**
	 * @param {Object[]} members
	 * @param {Object} assigned
	 * @returns {Object}
	 */
	static pickUser(members, assigned) {
		let member = members[Math.floor(Math.random() * members.length)].user_id;
		while (assigned.hasOwnProperty(member)) {
			member = members[Math.floor(Math.random() * members.length)].user_id;
		}
		return member;
	}

	static async getWerewolvesRoles() {
		const werewolves = await fetch("https://web/api/roles/2", { method: "GET" });
		return werewolves.json.roles;
	}
}
