<template>
  <BaseModal wrapper="role-presentation__wrapper">
    <header class="role-presentation__header">
      <svg
        class="popup__close"
        tabindex="0"
        @keydown.enter="store.close()"
        @keydown.space="store.close()"
        @click="store.close()"
      >
        <use href="/sprite.svg#cross" />
      </svg>
    </header>
    <div class="role-presentation__content">
      <div class="role-presentation__side-group">
        <img
          :src="props.role.image + '?w=125&dpr=2'"
          alt=""
        >
        <div class="role-presentation__side-group-right">
          <h3>{{ props.role.display_name }}</h3>
          <div>
            <p><span class="bold">Camp : </span> {{ props.role.team.display_name }}</p>
            <p><span class="bold">Limite par partie : </span> {{ props.role.limit === -1 ? 'Aucune' : props.role.limit }}</p>
          </div>
        </div>
      </div>

      <p>{{ props.role.description }}</p>
    </div>
  </BaseModal>
</template>

<script setup>
import { useStore } from "../../stores/modals/role-presentation.js";
import BaseModal from "./BaseModal.vue";

const props = defineProps({
	role: Object
});

const store = useStore();
</script>
