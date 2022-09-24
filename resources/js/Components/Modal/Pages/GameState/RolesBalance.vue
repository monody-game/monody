<template>
  <div class="roles-balance__container">
    <p class="roles-balance__status">
      La partie est <span>{{ currentStatus }}</span>
    </p>
    <div class="roles-balance__balance">
      <span class="roles-balance__werewolf" />
      <span class="roles-balance__villagers" />
    </div>
  </div>
</template>

<script setup>

import { computed, reactive, ref } from "vue";

const props = defineProps({
	selectedRoles: {
		type: Array,
		required: true
	}
});

const werewolfWidth = ref(0);
const villagerWidth = ref(0);
const varContainer = computed(() => document.documentElement.style);
const currentStatus = computed(() => getCurrentStatus());

const getCurrentStatus = function () {
	if (
		villagerWidth.value >= "50" &&
		villagerWidth.value <= "60" &&
		werewolfWidth.value >= "40" &&
		werewolfWidth.value <= "50"
	) {
		return "équilibrée";
	}
	if (werewolfWidth.value >= "40") {
		return "avantagée aux loups-garous";
	}
	if (villagerWidth.value >= "60") {
		return "avantagée aux villageois";
	}
};

const roles = reactive(props.selectedRoles);
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

werewolfWidth.value = Math.floor((werewolfWeight * 100) / totalWeight);
villagerWidth.value = Math.floor((villagerWeight * 100) / totalWeight);

if (werewolfWidth.value + villagerWidth.value !== 100) {
	const gap = 100 - (villagerWidth.value + werewolfWidth.value);
	werewolfWidth.value = werewolfWidth.value + gap;
}

varContainer.value.setProperty(
	"--villager-balance-width",
	villagerWidth.value + "%"
);

varContainer.value.setProperty(
	"--werewolf-balance-width",
	werewolfWidth.value + "%"
);
</script>
