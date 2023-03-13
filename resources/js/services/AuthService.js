import { useStore } from "../stores/user";

export default class AuthService {

	constructor() {
		this.store = useStore();
	}

	async getUser() {
		let res = await window.JSONFetch("/user", "GET");

		if (!res.status.toString().startsWith("2")) {
			return false;
		}

		const data = res.data;
		res = await window.JSONFetch("/exp/get", "GET");
		data.exp = res.data.exp;
		data.next_level = res.data.next_level;

		if (!data) {
			return false;
		}

		this.store.setUser({
			id: data.id,
			username: data.username,
			email: data.email,
			email_verified_at: data.email_verified_at,
			avatar: data.avatar,
			level: data.level,
			exp: data.exp,
			exp_needed: data.next_level,
			discord_linked_at: data.discord_linked_at
		});

		return true;
	}

	async logout() {
		await window.JSONFetch("/auth/logout", "POST");
		await this.store.$reset;
	}
}
