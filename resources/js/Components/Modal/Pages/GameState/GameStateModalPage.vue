<template>
  <div class="game-state__container">
    <RolesBalance :selectedRoles="getSelectedRoles"/>
    <p>Roles choisis :</p>
    <RoleShow
      v-for="role in getSelectedRoles"
      :key="getSelectedRoles.indexOf(role)"
      :selectedRole="role"
    />
  </div>
</template>

<script>
import RoleShow from "./RoleShow.vue";
import RolesBalance from "./RolesBalance.vue";

export default {
  name: "GameStateModalPage",
  components: { RoleShow, RolesBalance },
  computed: {
    getSelectedRoles () {
      const selectedIds = this.$store.getters.getSelectedRoles;
      const roles = this.$store.getters.getRoles;
      const selectedRoles = [];
      roles.forEach((role) => {
        if (selectedIds.indexOf(role.id) !== -1) {
          role.count = this.$store.getters.getRoleCountById(role.id);
          selectedRoles.push(role);
        }
      });
      return selectedRoles;
    },
  },
};
</script>

<style scoped></style>
