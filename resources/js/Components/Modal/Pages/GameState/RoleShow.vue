<template>
  <div class="role-show__container">
    <div class="role-show__image-group">
      <img
        :alt="role.name"
        :src="role.image"
        :title="role.display_name"
        class="role-show__image"
      >
      <p class="role-show__count">
        {{ role.count }}
      </p>
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
<script setup>
import { useStore } from "../../../../stores/GameCreationModal.js";
import { reactive } from "vue";

const props = defineProps({
	selectedRole: {
		type: Object,
		required: true
	}
});

const store = useStore();
const role = reactive(props.selectedRole);
role.team = store.getTeamById(role.team_id);
</script>
