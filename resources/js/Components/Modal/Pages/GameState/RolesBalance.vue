<template>
  <div class="roles-balance__container">
    <p class="roles-balance__status">
      La partie est <span>{{ current_status }}</span>
    </p>
    <div class="roles-balance__balance">
      <span class="roles-balance__werewolf"></span>
      <span class="roles-balance__villagers"></span>
    </div>
  </div>
</template>
<script>
export default {
  name: "RolesBalance",
  props: ["selectedRoles"],
  data () {
    return {
      werewolfWidth: 0,
      villagerWidth: 0,
    };
  },
  created () {
    this.calcBalancesWidths();
  },
  computed: {
    varContainer () {
      return document.documentElement.style;
    },
    current_status () {
      return this.getCurrentStatus();
    },
  },
  methods: {
    getCurrentStatus () {
      const werewolfCount = this.werewolfWidth;
      const villagerCount = this.villagerWidth;
      if (
        villagerCount >= "50" &&
        villagerCount <= "60" &&
        werewolfCount >= "40" &&
        werewolfCount <= "50"
      ) {
        return "équilibrée";
      }
      if (werewolfCount >= "40") {
        this.$store.commit("addError", "La partie est avantagée aux loups-garous");
        return "avantagée aux loups-garous";
      }
      if (villagerCount >= "60") {
        this.$store.commit("addError", "La partie est avantagée aux villageois");
        return "avantagée aux villageois";
      }
    },
    calcBalancesWidths () {
      const roles = this.selectedRoles;
      let villagerWeight = 0;
      let werewolfWeight = 0;

      roles.forEach((role) => {
        if (role.team_id === 1) {
          villagerWeight = villagerWeight + role.weight * role.count;
        } else if (role.team_id === 2) {
          werewolfWeight = werewolfWeight + role.weight * role.count;
        }
      });

      const totalWeight = villagerWeight + werewolfWeight;

      this.werewolfWidth = Math.floor((werewolfWeight * 100) / totalWeight);
      this.villagerWidth = Math.floor((villagerWeight * 100) / totalWeight);

      if (this.werewolfWidth + this.villagerWidth !== 100) {
        const gap = 100 - (this.villagerWidth + this.werewolfWidth);
        this.werewolfWidth = this.werewolfWidth + gap;
      }

      this.varContainer.setProperty(
        "--villager-balance-width",
        this.villagerWidth + "%"
      );

      this.varContainer.setProperty(
        "--werewolf-balance-width",
        this.werewolfWidth + "%"
      );
    },
  },
};
</script>
