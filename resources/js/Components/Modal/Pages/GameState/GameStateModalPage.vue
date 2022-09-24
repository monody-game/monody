<template>
  <div class="game-state__container">
    <RolesBalance :selected-roles="getSelectedRoles" />
    <p>Roles choisis :</p>
    <RoleShow
      v-for="role in getSelectedRoles"
      :key="getSelectedRoles.indexOf(role)"
      :selected-role="role"
    />
  </div>
</template>

<script setup>
import RoleShow from "./RoleShow.vue";
import RolesBalance from "./RolesBalance.vue";
import { useStore } from "../../../../stores/GameCreationModal.js";
import { computed } from "vue";

const getSelectedRoles = computed(() => {
	const store = useStore();
	const selectedIds = store.selectedRoles;
	const roles = store.roles;
	const selectedRoles = [];

	roles.forEach((role) => {
		if (selectedIds.indexOf(role.id) !== -1) {
			role.count = store.getRoleCountById(role.id);
			selectedRoles.push(role);
		}
	});

	return selectedRoles;
});
</script>
