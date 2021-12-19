<template>
  <div class="role-selector__container">
    <div class="role-selector__main">
      <button
        :class="count === 0 ? 'disable-hover' : ''"
        :disabled="count === 0"
        class="btn small"
        @click="substract()"
      >
        -
      </button>
      <img
        :alt="role.name"
        :src="role.image"
        :title="role.display_name"
        class="role-selector__image"
      />
      <button
        :class="count >= default_limit || count >= role.limit ? 'disable-hover' : ''"
        :disabled="count >= default_limit || count >= role.limit"
        class="btn small"
        @click="add()"
      >
        +
      </button>
    </div>
    <p class="role-selector__count">{{ count }}</p>
  </div>
</template>
<script>
export default {
  name: "RoleSelector",
  props: ["role"],
  data () {
    return {
      count: this.currentSelectedCount(),
      default_limit: 10,
    };
  },
  methods: {
    currentSelectedCount () {
      return this.$store.getters.getRoleCountById(this.role.id) ?? 0;
    },
    substract () {
      if (this.count === 0) {
        return;
      }
      this.$store.commit("removeSelectedRole", this.role.id);
      this.count = this.count - 1;
    },
    add () {
      const hasLimit = Object.keys(this.role).includes("limit");
      if (hasLimit && this.count >= this.role["limit"]) {
        return;
      } else if (!hasLimit && this.count >= this.default_limit) {
        return;
      }
      this.count = this.count + 1;
      this.$store.commit("addSelectedRole", this.role.id, this.count);
    },
  },
};
</script>
