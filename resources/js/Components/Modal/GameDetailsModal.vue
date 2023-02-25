<template>
  <BaseModal class="game-details">
    <header>
      <h3>Détails de la partie</h3>
    </header>
    <div class="modal__page game-details__wrapper">
      <div class="game-details__group">
        Créateur de la partie :
        <div class="game-details__owner">
          <img
            :src="gameStore.owner.avatar"
            :alt="gameStore.owner.username + '\'s avatar'"
          >
          <div class="game-details__owner-right">
            {{ gameStore.owner.username }}
            <div class="game-details__owner-stats">
              <div
                title="Niveau"
              >
                <svg>
                  <use href="/sprite.svg#level" />
                </svg>
                <p>{{ gameStore.owner.level }}</p>
              </div>
              <div
                title="Elo"
              >
                <svg>
                  <use href="/sprite.svg#elo" />
                </svg>
                <p>N/A</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="game-details__roles">
        Liste des rôles :
        <div class="roles__list">
          <RoleSelector
            v-for="role in roles"
            :key="role.id"
            :role="role"
            class="roles__item"
            :operations="false"
          />
        </div>
        <RolesBalance :selected-roles="roles" />
      </div>
    </div>
    <div class="modal__buttons">
      <div class="modal__buttons-right">
        <button
          class="btn medium"
          @click="store.close()"
        >
          Fermer
        </button>
      </div>
    </div>
  </BaseModal>
</template>

<script setup>
import { useStore as useGameStore } from "../../stores/game.js";
import { useStore } from "../../stores/modals/modal.js";
import BaseModal from "./BaseModal.vue";
import RoleSelector from "./Pages/Roles/RoleSelector.vue";
import RolesBalance from "./Pages/Roles/RolesBalance.vue";

const gameStore = useGameStore();
const store = useStore();
const roles = gameStore.roles;
</script>
