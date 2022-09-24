<template>
  <div class="register-page">
    <h1>S'inscrire</h1>
    <form class="register-page__form">
      <div>
        <input
          v-model="username"
          :class="errors.username.errored ? 'login-page__input-error' : ''"
          class="register-page__input"
          name="username"
          placeholder="Nom d'utilisateur"
          required
          type="text"
        >
        <span
          class="login-page__input-focused"
          :class="errors.username.errored ? 'login-page__input-focus-errored' : ''"
        />
        <p
          v-if="errors.username.errored"
          class="login-page__error"
        >
          {{ errors.username.text }}
        </p>
      </div>
      <div>
        <input
          v-model="email"
          :class="errors.email.errored ? 'login-page__input-error' : ''"
          class="register-page__input"
          name="email"
          placeholder="Email"
          required
          type="email"
        >
        <span
          class="login-page__input-focused"
          :class="errors.email.errored ? 'login-page__input-focus-errored' : ''"
        />
        <p
          v-if="errors.email.errored"
          class="login-page__error"
        >
          {{ errors.email.text }}
        </p>
      </div>
      <div>
        <input
          v-model="password"
          :class="errors.password.errored ? 'login-page__input-error' : ''"
          class="register-page__input"
          name="password"
          placeholder="Mot de passe"
          required
          type="password"
        >
        <span
          class="login-page__input-focused"
          :class="errors.password.errored ? 'login-page__input-focus-errored' : ''"
        />
        <p
          v-if="errors.password.errored"
          class="login-page__error"
        >
          {{ errors.password.text }}
        </p>
      </div>
      <div>
        <input
          v-model="password_confirmation"
          :class="errors.password_confirmation.errored ? 'login-page__input-error' : ''"
          class="register-page__input"
          name="password_confirmation"
          placeholder="Confirmez le mot de passe"
          required
          type="password"
        >
        <span
          class="login-page__input-focused"
          :class="errors.password_confirmation.errored ? 'login-page__input-focus-errored' : ''"
        />
        <p
          v-if="errors.password_confirmation.errored"
          class="login-page__error"
        >
          {{ errors.password_confirmation.text }}
        </p>
      </div>
      <div>
        <button
          class="register-page__button"
          type="submit"
          @keyup.enter="register()"
          @click.prevent="register()"
        >
          <DotsSpinner
            v-if="loading === true"
          />
          S'inscrire
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import DotsSpinner from "../../Components/Spinners/DotsSpinner.vue";
import { ref, watch } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const username = ref("");
const email = ref("");
const password = ref("");
const password_confirmation = ref("");
const loading = ref(false);
const errors = ref({
	text: "",
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

watch(username, () => validate(username.value, "username"));
watch(email, () => validate(email.value, "email"));
watch(password, () => validate(password.value, "password"));

const register = async function() {
	if (checkInput()) {
		loading.value = true;
		await window
			.JSONFetch("/auth/register", "POST", {
				username: username.value,
				email: email.value,
				password: password.value,
				password_confirmation: password_confirmation.value,
			});
		loading.value = false;
		await router.push("play");
	}
};

const checkInput = function () {
	errors.value.username.errored = username.value === "";
	errors.value.email.errored = email.value === "";
	errors.value.password.errored = password.value === "";
	errors.value.password_confirmation.errored = password_confirmation.value === "";

	if (username.value === "" || email.value === "" || password.value === "" || password_confirmation.value === "") {
		errors.value.text = "Merci de remplir tous les champs";
		loading.value = false;
		return false;
	}

	if (password.value !== password_confirmation.value) {
		errors.value.password.errored = true;
		errors.value.password_confirmation.errored = true;
		errors.value.text = "Les mots-de-passe doivent être identiques";
		loading.value = false;
		return false;
	}
	return true;
};

const validate = function (data, type) {
	if (type === "email") {
		if (data.match(/^([a-z.]+)@([a-z]+)\.([a-z]+)$/gm) === null) {
			errors.value.email.errored = true;
			errors.value.text = "Veuillez rentrer un email valide";
		} else {
			errors.value.email.errored = false;
			errors.value.text = "";
		}
	}
	if (type === "username") {
		if (data.length > 24) {
			errors.value.username.errored = true;
			errors.value.text = "Votre pseudo doit faire moins de 24 caractères";
		} else {
			errors.value.username.errored = false;
			errors.value.text = "";
		}
	}

	if (type === "password") {
		if (data.length < 6) {
			errors.value.password.errored = true;
			errors.value.text = "Votre mot-de-passe doit faire plus de 6 caractères";
		} else {
			errors.value.password.errored = false;
			errors.value.text = "";
		}
	}
};
</script>
