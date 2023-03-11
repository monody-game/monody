import { defineStore } from "pinia";

export const useStore = defineStore("user", {
	state: () => ({
		id: 0,
		username: "",
		email: "",
		avatar: "",
		level: 0,
		exp: 0,
		exp_needed: 0,
		theme: "dark"
	}),
	actions: {
		setUser(payload) {
			this.id = payload.id;
			this.username = payload.username;
			this.email = payload.email;
			this.avatar = payload.avatar;
			this.level = payload.level;
			this.exp = payload.exp;
			this.exp_needed = payload.exp_needed;
		},
	},
	getters: {
		getUser() {
			return {
				id: this.id,
				username: this.username,
				email: this.email,
				avatar: this.avatar,
				level: this.level,
				exp: this.exp,
				exp_needed: this.exp_needed
			};
		}
	}
});
