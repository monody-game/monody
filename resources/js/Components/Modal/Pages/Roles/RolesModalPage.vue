<template>
  <div class="roles__page">
    Selection des rôles :
    <div
      v-if="loading === true"
      class="roles__loader"
    >
      <LogoSpinner />
    </div>
    <div class="roles__list">
      <RoleSelector
        v-for="role in roles"
        :key="role.id"
        :role="role"
        class="roles__item"
      />
    </div>
    <RolesBalance :selected-roles="getSelectedRoles" />
  </div>
</template>

<script setup>
import RoleSelector from "./RoleSelector.vue";
import LogoSpinner from "../../../Spinners/LogoSpinner.vue";
import { useStore } from "../../../../stores/GameCreationModal.js";
import { computed, onMounted, ref } from "vue";
import RolesBalance from "./RolesBalance.vue";

const roles = ref([]);
const loading = ref(false);
const store = useStore();

onMounted(async () => {
	loading.value = true;
	await getRoles();
	await getTeams();
	loading.value = false;
});

const getSelectedRoles = computed(() => {
	const selectedIds = store.selectedRoles;
	const selectedRoles = [];

	for (const role of roles.value) {
		if (selectedIds.indexOf(role.id) !== -1) {
			role.count = store.getRoleCountById(role.id);
			selectedRoles.push(role);
		}
	}
	return selectedRoles;
});

const getRoles = async function() {
	if (store.roles.length === 0) {
		const res = await window.JSONFetch("/roles", "GET");
		const list = res.data;

		list.roles.forEach((role) => {
			role.image = window.location.origin + role.image;
			if (role.limit === null) {
				delete role.limit;
			}
		});

		roles.value = list.roles;
		store.roles = list.roles;
	} else {
		roles.value = store.roles;
	}
};

const getTeams = async function() {
	if (store.teams.length === 0) {
		const teams = await window.JSONFetch("/teams", "GET");
		store.teams = teams.data.teams;
	}
};
</script>
