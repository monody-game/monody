<template>
  <div class="auth-page__container">
    <div class="auth-page__wrapper">
      <router-link
        :to="{ name: 'home_page' }"
        class="auth-page__home-link"
      >
        <svg>
          <use href="/sprite.svg#back_chevron" />
        </svg>
        <p>
          Retour
        </p>
      </router-link>
      <div class="auth-page__form-wrapper">
        <h1>Mot de passe oublié ?</h1>
        <form
          action=""
          method="post"
          @submit.prevent
        >
          <div
            v-if="loading"
            class="auth-page__loading-group"
          >
            <div class="auth-page__loading-group-blur" />
            <DotsSpinner />
          </div>
          <InputComponent
            type="email"
            name="email"
            label="Email lié à votre compte"
            :errored="errors.email.errored"
            :error="errors.email.text"
            :note="false"
            @model="newEmail => email = newEmail"
          />
          <InputComponent
            v-if="route.params.token !== ''"
            type="password"
            label="Mot de passe"
            label_note="plus de 8 caractères"
            name="password"
            :errored="errors.password.errored"
            :error="errors.password.text"
            @model="(newPassword) => password = newPassword"
          />
          <InputComponent
            v-if="route.params.token !== ''"
            type="password"
            label="Confirmez le mot de passe"
            name="password_confirmation"
            :errored="password !== password_confirmation"
            error="La confirmation du mot de passe doit être identique au mot de passe"
            @model="newConfirmationPassword => password_confirmation = newConfirmationPassword"
          />
          <router-link
            class="auth-page__link"
            :to="{ name: 'login' }"
          >
            Vous vous souvenez de votre mot de passe ?
          </router-link>
          <div class="auth-page__submit-group">
            <router-link
              class="auth-page__link"
              :to="{ name: 'register' }"
            >
              Pas encore de compte ?
            </router-link>
            <button
              class="btn large"
              :disabled="email === ''"
              type="submit"
              @click="submit"
            >
              Soumettre la demande
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import DotsSpinner from "../../Components/Spinners/DotsSpinner.vue";
import InputComponent from "../../Components/Form/InputComponent.vue";
import { useStore } from "../../stores/alerts.js";

const route = useRoute();
const router = useRouter();
const alertStore = useStore();
const loading = ref(false);
const email = ref("");
const password = ref("");
const password_confirmation = ref("");

const errors = ref({
	email: {
		errored: false,
		text: ""
	},
	password: {
		errored: false,
		text: ""
	}
});

const submit = async () => {
	if (!checkInput()) return;

	loading.value = true;

	if (!route.params.token) {
		await window
			.JSONFetch("/auth/password/reset", "POST", {
				email: email.value,
			});

		loading.value = false;
	} else {
		const res = await window
			.JSONFetch("/auth/password/validate", "POST", {
				email: email.value,
				password: password.value,
				password_confirmation: password_confirmation.value,
				token: route.params.token
			});

		loading.value = false;

		if (res.ok) {
			await router.replace({ name: "login" });

			alertStore.addAlerts({
				info: "Veuillez vous reconnecter afin de confirmer le changement"
			});
		}
	}
};

watch(password, (newPassword) => {
	if (newPassword.length < 8) {
		errors.value.password.errored = true;
		errors.value.password.text = "Votre mot de passe doit faire plus de 8 caractères";
	} else {
		errors.value.password.errored = false;
		errors.value.password.text = "";
	}
});

watch(email, (newEmail) => {
	if (newEmail.match(/^([a-z.0-9]+)@([a-z]+)\.([a-z]+)$/gm) === null) {
		errors.value.email.errored = true;
		errors.value.email.text = "Veuillez rentrer un email valide";
	} else {
		errors.value.email.errored = false;
		errors.value.email.text = "";
	}
});

const checkInput = function () {
	errors.value.email.errored = errors.value.email.errored || email.value === "";
	errors.value.password.errored = errors.value.password.errored || password.value === "";

	if (email.value === "" || ((password.value === "" || password_confirmation.value === "") && route.params.token !== "")) {
		errors.value.email.text = "Merci de remplir tous les champs";
		loading.value = false;
		return false;
	}

	return true;
};
</script>
