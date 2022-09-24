<template>
  <div class="login-page">
    <h1>Se connecter</h1>
    <form class="login-page__form">
      <div>
        <input
          v-model="username"
          :class="errors.username.errored ? 'login-page__input-error' : ''"
          class="login-page__input"
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
          v-model="password"
          :class="errors.password.errored ? 'login-page__input-error' : ''"
          class="login-page__input"
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
      <div class="login-page__remember-wrapper">
        <label for="remember_me">Se souvenir de moi</label>
        <input
          id="remember_me"
          v-model="remember_me"
          class="login-page__remember-me"
          type="checkbox"
        >
      </div>
      <div>
        <button
          class="login-page__button"
          type="submit"
          @keyup.enter="login()"
          @click.prevent="login()"
        >
          <DotsSpinner
            v-if="loading === true"
          />
          Se connecter
        </button>
        <router-link
          class="login-page__no-account-link"
          to="/register"
        >
          Pas encore de compte ?
        </router-link>
      </div>
    </form>
  </div>
</template>

<script setup>
import DotsSpinner from "../../Components/Spinners/DotsSpinner.vue";
import { ref } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const username = ref("");
const password = ref("");
const remember_me = ref(false);
const loading = ref(false);
const errors = ref({
	username: {
		errored: false,
		text: ""
	},
	password: {
		errored: false,
		text: ""
	}
});

const login = async function () {
	if (username.value === "" || password.value === "") {
		errors.value.username.errored = true;
		errors.value.password.errored = true;
		errors.value.password.text = "Vous devez rentrer vos identifiants";
		return;
	}
	loading.value = true;
	await window
		.JSONFetch("/auth/login", "POST", {
			username: username.value,
			password: password.value,
			remember_me: remember_me.value,
		});

	loading.value = false;
	await router.push("play");
};
</script>
