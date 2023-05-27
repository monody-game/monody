<template>
  <div class="roles__page">
    <span class="bold">Selection des rôles :</span> <span class="roles__note">(Cliquez sur un rôle pour en savoir plus)</span>
    <div
      v-if="loading === true"
      class="roles__loader"
    >
      <LogoSpinner />
    </div>
    <div class="team__list">
      <div class="roles__villagers">
        <div class="roles__team-description">
          <svg>
            <use href="/sprite.svg#villager" />
          </svg>
          <span class="bold">
            Villageois
          </span>
        </div>
        <div class="roles__list">
          <RoleSelector
            v-for="role in villagers"
            :key="role.id"
            :role="role"
            class="roles__item"
            :presentable="true"
          />
        </div>
      </div>
      <div class="roles__werewolves">
        <div class="roles__team-description">
          <svg>
            <use href="/sprite.svg#werewolves" />
          </svg>
          <span class="bold">
            Loup-garous
          </span>
        </div>
        <div class="roles__list">
          <RoleSelector
            v-for="role in werewolves"
            :key="role.id"
            :role="role"
            class="roles__item"
            :presentable="true"
          />
        </div>
      </div>
      <div class="roles__loners roles__list">
        <div class="roles__team-description">
          <svg>
            <use href="/sprite.svg#loners" />
          </svg>
          <span class="bold">
            Solitaires
          </span>
        </div>
        <div class="roles__list">
          <RoleSelector
            v-for="role in loners"
            :key="role.id"
            :role="role"
            class="roles__item"
            :presentable="true"
          />
        </div>
      </div>
    </div>
    <RolesBalance :selected-roles="getSelectedRoles" />
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import { useStore } from "../../../../stores/modals/game-creation-modal.js";
import { useStore as useModalStore } from "../../../../stores/modals/modal.js";
import RoleSelector from "./RoleSelector.vue";
import LogoSpinner from "../../../Spinners/LogoSpinner.vue";
import RolesBalance from "./RolesBalance.vue";

const roles = ref([]);
const villagers = ref([]);
const werewolves = ref([]);
const loners = ref([]);
const loading = ref(true);
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
			if (role.limit === -1) {
				delete role.limit;
			}
		});

		roles.value = list.roles;
		store.roles = list.roles;
	} else {
		roles.value = store.roles;
	}

	villagers.value = roles.value.filter(role => role.team.id === 1);
	werewolves.value = roles.value.filter(role => role.team.id === 2);
	loners.value = roles.value.filter(role => role.team.id === 3);
};

const getTeams = async function() {
	if (store.teams.length === 0) {
		const teams = await window.JSONFetch("/teams", "GET");
		store.teams = teams.data.teams;
	}
};
</script>
