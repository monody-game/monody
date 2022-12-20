import { defineStore } from "pinia";

export const useStore = defineStore("alerts", {
	state: () => {
		return {
			alerts: {
				"1234": {
					type: "success",
					content: "Ca marche !"
				}
			},
			popups: {}
		};
	},
	actions: {
		addAlerts(alerts) {
			for (const type in alerts) {
				const id = Math.random().toString(36);
				this.alerts[id] = {
					type,
					content: alerts[type]
				};
			}
		},
		dropAlert(id) {
			setTimeout(() => {
				delete this.alerts[id];
			}, 1000);
		}
	}
});
