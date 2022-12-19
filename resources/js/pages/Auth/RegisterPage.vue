<template>
  <div class="auth-page__container">
    <div class="auth-page__wrapper">
      <div class="auth-page__form-wrapper">
        <h1>S'inscrire</h1>
        <form
          class="register-page__form"
          method="post"
          action=""
          @submit.prevent
        >
          <div
            v-if="loading"
            class="auth-page__loading-group"
          >
            <div class="auth-page__loading-group-blur" />
            <DotsSpinner />
          </div>
          <div
            class="auth-page__form-group"
            :data-is-invalid="errors.username.errored"
          >
            <label for="username">Nom d'utilisateur <span class="auth-page__input-notice">(Entre 3 et 24 caractères)</span></label>
            <input
              id="username"
              v-model="username"
              type="text"
              name="username"
            >
            <svg v-if="errors.username.errored">
              <use href="/sprite.svg#error" />
            </svg>
            <p v-if="errors.username.errored">
              {{ errors.username.text }}
            </p>
          </div>
          <div
            class="auth-page__form-group"
            :data-is-invalid="errors.email.errored"
          >
            <label for="email">
              Email
              <NoticeComponent title="Pourquoi dois-je donner cette information ?">
                Votre email nous est utile lorsque vous perdez votre mot de passe. C’est également un moyen d’identification (connection, connection de votre compte Discord à Monody)
              </NoticeComponent>
            </label>
            <input
              id="email"
              v-model="email"
              type="email"
              name="email"
            >
            <svg v-if="errors.email.errored">
              <use href="/sprite.svg#error" />
            </svg>
            <p v-if="errors.email.errored">
              {{ errors.email.text }}
            </p>
          </div>
          <div
            class="auth-page__form-group"
            :data-is-invalid="errors.password.errored"
          >
            <label for="password">Mot de passe <span class="auth-page__input-notice">(plus de 8 caractères)</span></label>
            <input
              id="password"
              v-model="password"
              type="password"
              name="password"
            >
            <VisibilityToggle
              class="auth-page__show-password"
              field="password"
            />
            <svg
              v-if="errors.password.errored"
              class="auth-page__error-icon"
            >
              <use href="/sprite.svg#error" />
            </svg>
            <p v-if="errors.password.errored">
              {{ errors.password.text }}
            </p>
          </div>
          <div
            class="auth-page__form-group"
            :data-is-invalid="password !== password_confirmation"
          >
            <label for="password_confirmation">Confirmez le mot de passe</label>
            <input
              id="password_confirmation"
              v-model="password_confirmation"
              type="password"
              name="password_confirmation"
            >
            <VisibilityToggle
              class="auth-page__show-password"
              field="password_confirmation"
            />
            <svg
              v-if="password !== password_confirmation"
              class="auth-page__error-icon"
            >
              <use href="/sprite.svg#error" />
            </svg>
            <p v-if="password !== password_confirmation">
              La confirmation du mot de passe doit être identique au mot de passe
            </p>
          </div>
          <div class="auth-page__submit-group">
            <router-link
              class="auth-page__link"
              to="login"
            >
              Vous possédez déjà un compte ?
            </router-link>
            <button
              class="btn large"
              type="submit"
              :disabled="username === '' || email === '' || password === '' || password_confirmation === ''"
              @click="register"
            >
              S'inscrire
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import DotsSpinner from "../../Components/Spinners/DotsSpinner.vue";
import { ref, watch } from "vue";
import { useRouter } from "vue-router";
import NoticeComponent from "../../Components/NoticeComponent.vue";
import VisibilityToggle from "../../Components/Form/VisibilityToggle.vue";

const router = useRouter();
const username = ref("");
const email = ref("");
const password = ref("");
const password_confirmation = ref("");
const loading = ref(false);

const errors = ref({
	username: {
		errored: false,
		text: ""
	},
	email: {
		errored: false,
		text: ""
	},
	password: {
		errored: false,
		text: ""
	},
	password_confirmation: {
		errored: false,
		text: ""
	}
});

watch(username, (newUsername) => {
	if (newUsername.length > 24) {
		errors.value.username.errored = true;
		errors.value.username.text = "Votre nom d'utilsateur doit faire moins de 24 caractères";
	} else if (newUsername.length < 3) {
		errors.value.username.errored = true;
		errors.value.username.text = "Votre nom d'utilsateur doit faire plus de 3 caractères";
	} else if (newUsername.includes(" ")) {
		errors.value.username.errored = true;
		errors.value.username.text = "Il ne doit pas y avoir d'espaces dans votre pseudo";
	} else {
		errors.value.username.errored = false;
		errors.value.username.text = "";
	}
});

watch(password, (newPassword) => {
	if (newPassword.length < 8) {
		errors.value.password.errored = true;
		errors.value.password.text = "Votre mot-de-passe doit faire plus de 8 caractères";
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

const register = async function() {
	if (checkInput()) {
		loading.value = true;
		const res = await window
			.JSONFetch("/auth/register", "POST", {
				username: username.value,
				email: email.value,
				password: password.value,
				password_confirmation: password_confirmation.value,
			});
		loading.value = false;

		if (res.status === 422) {
			const validationErrors = res.data.errors;
			for (const field in validationErrors) {
				if (validationErrors[field].includes("validation.unique")) {
					errors.value[field].text = "Un compte utilisant cette valeur existe déjà";
					errors.value[field].errored = true;
				}
			}
			return;
		}

		await router.push("play");
	}
};

const checkInput = function () {
	errors.value.username.errored = errors.value.username.errored || username.value === "";
	errors.value.email.errored = errors.value.email.errored || email.value === "";
	errors.value.password.errored = errors.value.password.errored || password.value === "";
	errors.value.password_confirmation.errored = errors.value.password_confirmation.errored || password_confirmation.value === "";

	if (username.value === "" || email.value === "" || password.value === "" || password_confirmation.value === "") {
		errors.value.text = "Merci de remplir tous les champs";
		loading.value = false;
		return false;
	}

	return true;
};
</script>
