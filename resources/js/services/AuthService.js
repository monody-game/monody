import { useStore } from "../stores/user";

export default class AuthService {
	constructor() {
		this.store = useStore();
	}

	async isLoggedIn() {
		let res = await window.JSONFetch("/auth/logged", "POST");

		return res.ok;
	}

	async getUser() {
		let res = await window.JSONFetch("/user", "GET");

		if (!res.status.toString().startsWith("2")) {
			return false;
		}

		const data = res.data.user;

		if (!data) {
			return false;
		}

		res = await window.JSONFetch("/exp/get");
		data.exp = res.data.exp.exp;
		data.next_level = res.data.exp.next_level;

		this.store.setUser({
			id: data.id,
			username: data.username,
			email: data.email,
			email_verified_at: data.email_verified_at,
			avatar: data.avatar,
			level: data.level,
			exp: data.exp,
			exp_needed: data.next_level,
			discord_linked_at: data.discord_linked_at,
		});

		return true;
	}

	async logout() {
		await window.JSONFetch("/auth/logout", "POST");
		await this.store.$reset;
	}
}
