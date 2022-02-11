<template>
  <div class="modal__background" @click="closeModal()">
    <div
      ref="modal"
      aria-modal="true"
      class="modal__main"
      tabindex="-1"
      @click.stop=""
      @keyup.esc="closeModal()"
    >
      <p>Création d'une partie ({{ currentPage }}/{{ totalPage }})</p>
      <div class="modal__page">
        <RolesModalPage v-if="currentPage === 1"/>
        <GameStateModalPage v-if="currentPage === 2"/>
        <ShareModalPage v-if="currentPage === 3"/>
      </div>
      <div class="modal__buttons">
        <button class="btn large" @click="closeModal()">Annuler</button>
        <div class="modal__buttons-right">
          <button
            :class="currentPage === 1 ? 'disable-hover' : ''"
            :disabled="currentPage === 1"
            class="btn large"
            @click="previousPage()"
          >
            Précédent
          </button>
          <button
            v-if="currentPage !== totalPage"
            :class="notEnoughSelectedRoles() === true ? 'disable-hover' : ''"
            :disabled="notEnoughSelectedRoles()"
            class="btn large"
            @click="nextPage()"
          >
            Suivant
          </button>
          <button
            v-if="currentPage === totalPage"
            class="btn large"
            @click="finish()"
          >
            Terminer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import RolesModalPage from "@/Components/Modal/Pages/Roles/RolesModalPage";
import GameStateModalPage from "@/Components/Modal/Pages/GameState/GameStateModalPage";
import ShareModalPage from "@/Components/Modal/Pages/ShareModalPage";
import { useStore } from "@/stores/modal"

export default {
  name: "NewGameModal",
  components: {
    RolesModalPage,
    GameStateModalPage,
    ShareModalPage,
  },
  data () {
    return {
      currentPage: 1,
      totalPage: 3,
      error: "",
      store: useStore()
    };
  },
  mounted () {
    this.$refs.modal.focus();
  },
  methods: {
    notEnoughSelectedRoles () {
      const selectedRoles = this.store.selectedRoles;
      return selectedRoles.length < 5;
    },
    closeModal () {
      this.store.isOpenned = false;
    },
    async nextPage () {
      if (this.currentPage + 1 > this.totalPage) {
        return;
      }

      if (this.currentPage === 2) {
          this.currentPage = this.currentPage + 1;
          await window.JSONFetch("/game/new", "POST", {
            roles: this.store.selectedRoles,
            is_started: false,
            users: []
          });
      } else {
        this.currentPage = this.currentPage + 1;
      }
    },
    finish () {
      this.closeModal();
    },
    previousPage () {
      if (this.currentPage + 1 < this.totalPage) {
        return;
      }
      this.currentPage = this.currentPage - 1;
    },
  },
};
</script>
<style scoped></style>
