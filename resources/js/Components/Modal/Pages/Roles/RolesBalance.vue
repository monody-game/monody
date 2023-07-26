<template>
	<div class="roles-balance__container">
		<div
			class="roles-balance__balance"
			:title="$t('new_game.balance', [currentStatus])"
		>
			<span class="roles-balance__villagers" />
			<span class="roles-balance__bubble">{{ roleCount }}</span>
			<span class="roles-balance__werewolf" />
		</div>
	</div>
</template>

<script setup>
import { computed, nextTick, ref, watch } from "vue";
import { useI18n } from "vue-i18n";

const props = defineProps({
	selectedRoles: {
		type: Array,
		required: true,
	},
});

const roleCount = ref(
	props.selectedRoles.reduce(
		(accumulator, current) => accumulator + current.count,
		0,
	),
);
const { t } = useI18n();
const werewolfWidth = ref(50);
const villagerWidth = ref(50);
const varContainer = computed(() => document.documentElement.style);
const currentStatus = computed(() => getCurrentStatus());

const getCurrentStatus = function () {
	if (
		villagerWidth.value >= 50 &&
		villagerWidth.value <= 60 &&
		werewolfWidth.value >= 40 &&
		werewolfWidth.value <= 50
	) {
		return t("new_game.balanced");
	}
	if (werewolfWidth.value >= 40) {
		return t("new_game.werewolves_advantage");
	}
	if (villagerWidth.value >= 60) {
		return t("new_game.werewolves_advantage");
	}
};

const render = (roles) => {
	let villagerWeight = 0;
	let werewolfWeight = 0;

	roleCount.value = roles.reduce(
		(accumulator, current) => accumulator + current.count,
		0,
	);

	if (roles.length === 0) {
		varContainer.value.setProperty("--villager-balance-width", "50%");

		varContainer.value.setProperty("--werewolf-balance-width", "50%");

		return;
	}

	for (const role of roles) {
		if (role.team.name === "loners") continue;

		if (role.team.id === 1) {
			villagerWeight = villagerWeight + role.weight * role.count;
		} else if (role.team.id === 2) {
			werewolfWeight = werewolfWeight + role.weight * role.count;
		}
	}

	const totalWeight = villagerWeight + werewolfWeight;

	if (totalWeight === 0) return;

	werewolfWidth.value = Math.floor((werewolfWeight * 100) / totalWeight);
	villagerWidth.value = Math.floor((villagerWeight * 100) / totalWeight);

	if (werewolfWidth.value + villagerWidth.value !== 100) {
		const gap = 100 - (villagerWidth.value + werewolfWidth.value);
		werewolfWidth.value = werewolfWidth.value + gap;
	}

	varContainer.value.setProperty(
		"--villager-balance-width",
		villagerWidth.value + "%",
	);

	varContainer.value.setProperty(
		"--werewolf-balance-width",
		werewolfWidth.value + "%",
	);
};

nextTick(() => render(props.selectedRoles));

watch(
	() => props.selectedRoles,
	(roles) => render(roles),
);
</script>
