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
        <span class="login-page__input-focused" />
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
        <span class="login-page__input-focused" />
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
          <LogoSpinner
            v-if="loading === true"
            class="spinner__cloud-white"
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

<script>
import LogoSpinner from "../../Components/Spinners/LogoSpinner.vue";
import { useStore } from "../../stores/user.js";

export default {
	name: "LoginPage",
	components: {
		LogoSpinner: LogoSpinner
	},
	data() {
		return {
			store: useStore(),
			username: "",
			password: "",
			remember_me: false,
			loading: false,
			errors: {
				username: {
					errored: false,
					text: ""
				},
				password: {
					errored: false,
					text: ""
				}
			}
		};
	},
	methods: {
		login: async function () {
			if (this.username === "" || this.password === "") {
				this.errors.username.errored = true;
				this.errors.password.errored = true;
				this.errors.password.text = "Vous devez rentrer vos identifiants";
				return;
			}
			this.loading = true;
			await window
				.JSONFetch("/auth/login", "POST", {
					username: this.username,
					password: this.password,
					remember_me: this.remember_me,
				});

			this.loading = false;
			await this.$router.push("play");
		},
	},
};
</script>
