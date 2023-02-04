<template>
  <BaseModal wrapper="role-assignation__modal-background">
    <div
      :class="animationEnded ? 'role-assignation__wrapper ' + roleOverlay : 'role-assignation__wrapper-large ' + roleOverlay"
    >
      <div
        :class="animationEnded ? 'role-assignation__roles' : 'role-assignation__roles-large'"
      >
        <template
          v-for="n in 15"
          :key="n"
        >
          <div
            v-for="role in [...roles].sort(() => Math.random() - 0.5)"
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
    <p
      v-show="animationEnded"
      ref="roleText"
    >
      <span><span>Vous êtes : <span class="bold">{{ assignedRole.display_name.toLowerCase() }}</span>,</span></span> <br> <span><span>du camp des <span class="bold">{{ assignedRole.team.display_name.toLowerCase() }}</span></span></span>
    </p>
  </BaseModal>
</template>

<script setup>
import { onMounted, ref } from "vue";
import BaseModal from "./Modal/BaseModal.vue";

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

const animationEnded = ref(false);
const roleText = ref(null);

const roles = ref(props.roles);
const assignedRole = roles.value.filter(role => role.id === parseInt(props.assignedRole))[0];
const roleOverlay = ref("");

document.addEventListener("animationend", (e) => {
	if (e.animationName === "slideRoles") {
		animationEnded.value = true;
		roleOverlay.value = "role-assignation-overlay__" + assignedRole.team.name;
	}
});

onMounted(() => {
	const children = roleText.value.children;
	let delay = 0;

	for (const span of children) {
		if (span.localName !== "span") {
			continue;
		}

		span.firstChild.style.animationDelay = `${delay}s`;
		delay += 0.7;
	}
});

document.documentElement.style.setProperty("--role-assignation-transform-length", `-${roles.value.length * 15 * 100}%`);
</script>
