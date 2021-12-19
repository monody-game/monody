<template>
  <div class="roles__page">
    Choisissez les roles parmis les suivants :
    <DotsSpinner v-if="loading" class="roles__loader"/>
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
import DotsSpinner from "@/Components/Spinners/DotsSpinner";

export default {
  name: "RolesModalPage",
  components: {
    RoleSelector,
    DotsSpinner: DotsSpinner
  },
  data () {
    return {
      roles: [],
      loading: false
    };
  },
  mounted () {
    (async () => {
      this.loading = true;
      await this.getRoles();
      await this.getTeams();
      this.loading = false;
    })();
  },
  methods: {
    async getRoles () {
      if (this.$store.getters.getRoles.length === 0) {
        const res = await window.JSONFetch("/roles", "GET");
        const list = res.data;
        list.roles.forEach((role) => {
          role.image = "http://localhost:8000" + role.image;
          if (role.limit === null) {
            delete role.limit;
          }
        });
        this.roles = list.roles;
        this.$store.commit("setRoles", list.roles);
      } else {
        this.roles = this.$store.getters.getRoles;
      }
    },
    async getTeams () {
      if (this.$store.getters.getTeams.length === 0) {
        const teams = await window.JSONFetch("/teams", "GET");
        this.$store.commit("setTeams", teams.data.teams);
      }
    },
  },
};
</script>

<style scoped></style>
