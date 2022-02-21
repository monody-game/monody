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
import RolesModalPage from "@/Components/Modal/Pages/Roles/RolesModalPage.vue";
import GameStateModalPage from "@/Components/Modal/Pages/GameState/GameStateModalPage.vue";
import ShareModalPage from "@/Components/Modal/Pages/ShareModalPage.vue";
import { useStore } from "@/stores/modal.js"

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
      store: useStore(),
      gameId: 0,
    };
  },
  mounted () {
    this.$refs.modal.focus();
  },
  methods: {
    notEnoughSelectedRoles () {
      const selectedRoles = this.store.selectedRoles;
      //return selectedRoles.length < 5;
      //TODO: uncomment line above
      return selectedRoles.length < 2;
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
          const res = await window.JSONFetch("/game/new", "POST", {
            roles: this.store.selectedRoles,
            is_started: false,
            users: []
          });
          this.gameId = res.data.game.id
      } else {
        this.currentPage = this.currentPage + 1;
      }
    },
    finish () {
      this.closeModal();
      if(this.gameId !== 0) {
        this.$router.push('/game/' + this.gameId);
      }
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
