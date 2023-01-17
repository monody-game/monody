<template>
  <BaseModal>
    <div class="role-assignation__wrapper">
      <div class="role-assignation__roles">
        <template
          v-for="n in 6"
          :key="n"
        >
          <div
            v-for="role in roles.sort(() => Math.random() - 0.5)"
            :key="role.id"
          >
            <img
              :src="role.image"
              :alt="role.display_name"
            >
          </div>
        </template>
        <div>
          <img
            :src="assignedRole.image"
            :alt="assignedRole.display_name"
          >
        </div>
      </div>
    </div>
  </BaseModal>
</template>

<script setup>
import { ref } from "vue";
import BaseModal from "./Modal/BaseModal.vue";
import { useStore } from "../stores/modal.js";

const props = defineProps({
	roles: {
		type: Array,
		required: true,
	},
	assignedRole: {
		type: String,
		required: true
	}
});

const roles = ref(props.roles);
const assignedRole = roles.value.filter(role => role.id === parseInt(props.assignedRole))[0];
console.log(assignedRole);
useStore().opennedModal = "role-assignation";

document.documentElement.style.setProperty("--role-assignation-transform-length", `-${roles.value.length * 6 * 100}%`);
</script>
