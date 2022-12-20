import { defineStore } from "pinia";

export const useStore = defineStore("alerts", {
	state: () => {
		return {
			alerts: {
				"1DEda": {
					content: "Test d'alerte avec la pitite barre en bas :)",
					type: "error"
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
