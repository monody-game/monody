<template>
  <BaseModal>
    <header>
      <h3>Modification du profil</h3>
    </header>
    <div class="profile-modal__wrapper">
      <div class="profile-modal__side-group">
        <img
          class="profile-modal__avatar"
          :src="userStore.avatar + '?h=80&dpr=2'"
          :alt="userStore.username + '\'s avatar'"
        >
        <InputComponent
          type="text"
          name="username"
          label="Pseudo"
          label-note="entre 3 et 24 caractères"
          :errored="usernameErrors.errored"
          :error="usernameErrors.text"
          :value="userStore.username"
          @model="newUsername => username = newUsername"
        />
      </div>
      <InputComponent
        type="email"
        name="email"
        label="Email"
        error="Veuillez rentrer un email valide"
        :errored="email.match(/^([a-z.0-9]+)@([a-z]+)\.([a-z]+)$/gm) === null"
        :value="userStore.email"
        @model="newEmail => email = newEmail"
      />
      <div class="profile-modal__connections">
        <label for="connections">Connexions</label>
        <div class="profile-modal__connections-discord">
          <div class="profile-modal__connections-side-group">
            <svg>
              <use href="/sprite.svg#discord" />
            </svg>
            <p>Compte discord: N/A (n/a)</p>
          </div>
          <button
            class="btn medium"
            @click="soon"
          >
            Connecter
          </button>
        </div>
      </div>
    </div>
    <div class="modal__buttons">
      <button
        class="btn medium"
        @click="modalStore.close()"
      >
        Annuler
      </button>
      <div class="modal__buttons-right">
        <button
          :disabled="false"
          class="btn medium"
          @click="updateProfile"
        >
          Mettre à jour
        </button>
      </div>
    </div>
  </BaseModal>
</template>

<script setup>
import { ref, watch } from "vue";
import { useStore } from "../../stores/user.js";
import { useStore as useModalStore } from "../../stores/modals/modal.js";
import { useStore as useAlertStore } from "../../stores/alerts.js";
import BaseModal from "./BaseModal.vue";
import InputComponent from "../Form/InputComponent.vue";

const userStore = useStore();
const modalStore = useModalStore();
const alertStore = useAlertStore();

const username = ref(userStore.username);
const email = ref(userStore.email);
const usernameErrors = ref({});

watch(username, (newUsername) => {
	if (newUsername.length > 24) {
		usernameErrors.value.errored = true;
		usernameErrors.value.text = "Votre nom d'utilsateur doit faire moins de 24 caractères";
	} else if (newUsername.length < 3) {
		usernameErrors.value.errored = true;
		usernameErrors.value.text = "Votre nom d'utilsateur doit faire plus de 3 caractères";
	} else if (newUsername.includes(" ")) {
		usernameErrors.value.errored = true;
		usernameErrors.value.text = "Il ne doit pas y avoir d'espaces dans votre pseudo";
	} else {
		usernameErrors.value.errored = false;
		usernameErrors.value.text = "";
	}
});

const updateProfile = async () => {
	const modifiedFields = {};

	if (userStore.username !== username.value) {
		modifiedFields.username = username.value;
	}

	if (userStore.email !== email.value) {
		modifiedFields.email = email.value;
	}

	if (Object.keys(modifiedFields).length === 0) {
		modalStore.close();
		alertStore.addAlerts({
			info: "Aucune changement effectué"
		});

		return;
	}

	const res = await window.JSONFetch("/user", "PATCH", modifiedFields);

	userStore.setUser({
		id: res.data.id,
		username: res.data.username,
		email: res.data.email,
		avatar: res.data.avatar,
		level: res.data.level,
		exp: userStore.exp
	});

	alertStore.addAlerts({
		success: "Profil modifié avec succès !"
	});

	modalStore.close();
};

const soon = () => {
	alertStore.addAlerts({
		info: "Un jour, peut-être ..."
	});
};
</script>
