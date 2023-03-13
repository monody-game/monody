import { defineStore } from "pinia";

export const useStore = defineStore("user", {
	state: () => ({
		id: 0,
		username: "",
		email: "",
		email_verified_at: null,
		discord_linked_at: null,
		avatar: "",
		level: 0,
		exp: 0,
		exp_needed: 0,
		theme: "dark",
	}),
	actions: {
		setUser(payload) {
			this.id = payload.id;
			this.username = payload.username;
			this.email = payload.email;
			this.email_verified_at = payload.email_verified_at;
			this.avatar = payload.avatar;
			this.level = payload.level;
			this.exp = payload.exp;
			this.exp_needed = payload.exp_needed;
			this.discord_linked_at = payload.discord_linked_at;
		},
	},
	getters: {
		getUser() {
			return {
				id: this.id,
				username: this.username,
				email: this.email,
				email_verified_at: this.email_verified_at,
				avatar: this.avatar,
				level: this.level,
				exp: this.exp,
				exp_needed: this.exp_needed,
				discord_linked_at: this.discord_linked_at
			};
		}
	}
});
