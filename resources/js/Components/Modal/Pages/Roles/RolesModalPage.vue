<template>
  <div class="roles__page">
    Choisissez les roles parmis les suivants :
    <LogoSpinner
      v-if="loading"
      class="roles__loader"
    />
    <div class="roles__list">
      <RoleSelector
        v-for="role in roles"
        :key="role.id"
        :role="role"
        class="roles__item"
      />
    </div>
  </div>
</template>

<script>
import RoleSelector from "./RoleSelector.vue";
import LogoSpinner from "../../../Spinners/LogoSpinner.vue";
import { useStore } from "../../../../stores/modal.js";

export default {
	name: "RolesModalPage",
	components: {
		RoleSelector,
		LogoSpinner: LogoSpinner
	},
	data() {
		return {
			roles: [],
			loading: false,
			store: useStore()
		};
	},
	mounted() {
		(async () => {
			this.loading = true;
			await this.getRoles();
			await this.getTeams();
			this.loading = false;
		})();
	},
	methods: {
		async getRoles() {
			if (this.store.roles.length === 0) {
				const res = await window.JSONFetch("/roles", "GET");
				const list = res.data;
				list.roles.forEach((role) => {
					role.image = "https://localhost" + role.image;
					if (role.limit === null) {
						delete role.limit;
					}
				});
				this.roles = list.roles;
				this.store.roles = list.roles;
			} else {
				this.roles = this.store.roles;
			}
		},
		async getTeams() {
			if (this.store.teams.length === 0) {
				const teams = await window.JSONFetch("/teams", "GET");
				this.store.teams = teams.data.teams;
			}
		},
	},
};
</script>
