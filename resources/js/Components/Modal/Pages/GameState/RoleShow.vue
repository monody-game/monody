<template>
  <div class="role-show__container">
    <div class="role-show__image-group">
      <img
        :alt="role.name"
        :src="role.image"
        :title="role.display_name"
        class="role-show__image"
      />
      <p class="role-show__count">{{ role.count }}</p>
    </div>
    <div class="role-show_informations">
      <p>{{ role.display_name }}</p>
      <p>
        Camp des
        <span class="role-show__team-display-name">{{
            role.team.display_name
          }}</span>
      </p>
    </div>
  </div>
</template>
<script>
export default {
  name: "RoleShow",
  props: ["selectedRole"],
  data () {
    return {
      role: this.selectedRole,
    };
  },
  created () {
    this.role.team = this.$store.getters.getTeamById(this.role.team_id);
  },
};
</script>
